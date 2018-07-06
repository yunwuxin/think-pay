<?php
// +----------------------------------------------------------------------
// | ThinkPay
// +----------------------------------------------------------------------
// | Copyright (c) yunwuxin All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace yunwuxin\pay\channel;

use DomainException;
use GuzzleHttp\Psr7\Response;
use Jenssegers\Date\Date;
use Symfony\Component\OptionsResolver\OptionsResolver;
use think\Cache;
use think\helper\Str;
use think\Request;
use yunwuxin\pay\Channel;
use yunwuxin\pay\entity\PurchaseResult;
use yunwuxin\pay\entity\TransferResult;
use yunwuxin\util\http\Client;
use yunwuxin\util\http\Options;
use yunwuxin\pay\interfaces\Payable;
use yunwuxin\pay\interfaces\Refundable;
use yunwuxin\pay\interfaces\Transferable;

class Wechat extends Channel
{
    const TYPE_NATIVE = 'NATIVE';
    const TYPE_JSAPI  = 'JSAPI';
    const TYPE_APP    = 'APP';

    protected $endpoint = 'https://api.mch.weixin.qq.com';

    protected $options;

    protected $test = false;

    public function __construct($options)
    {
        $resolver = new OptionsResolver();

        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['app_id', 'mch_id', 'key']);

        $resolver->setDefined(['public_key', 'private_key']);

        $resolver->setNormalizer('public_key', function (\Symfony\Component\OptionsResolver\Options $options, $value) {
            if (!empty($value) && !is_file($value)) {
                $fn = tempnam(sys_get_temp_dir(), 'think-pay-wechat-public-');
                file_put_contents($fn, "-----BEGIN CERTIFICATE-----\n" . wordwrap($value, 64, "\n", true) . "\n-----END CERTIFICATE-----");
                return $fn;
            }

            return $value;
        });

        $resolver->setNormalizer('private_key', function (\Symfony\Component\OptionsResolver\Options $options, $value) {
            if (!empty($value) && !is_file($value)) {
                $fn = tempnam(sys_get_temp_dir(), 'think-pay-wechat-private-');
                file_put_contents($fn, "-----BEGIN PRIVATE KEY-----\n" . wordwrap($value, 64, "\n", true) . "\n-----END PRIVATE KEY-----");
                return $fn;
            }

            return $value;
        });
    }

    public function setTest()
    {
        $this->test = true;

        $this->options['key'] = $this->getSignKey();
    }

    protected function getSignKey()
    {
        return Cache::remember('wechat_sandbox_key', function () {
            $params         = [
                'mch_id'    => $this->options['mch_id'],
                'nonce_str' => Str::random()
            ];
            $params['sign'] = $this->generateSign($params);

            $response = Client::post($this->endpoint('pay/getsignkey'), Options::makeWithBody(array2xml($params)));

            $result = $this->validateResponse($response);

            return $result['sandbox_signkey'];
        });
    }

    protected function generateSign($params)
    {
        unset($params['sign']);
        ksort($params);
        $query = urldecode(http_build_query($params));
        $query .= "&key={$this->options['key']}";
        return strtoupper(md5($query));
    }

    protected function endpoint($uri = '')
    {
        if ($this->test) {
            return $this->endpoint . '/sandboxnew/' . $uri;
        }

        return $this->endpoint . '/' . $uri;
    }

    /**
     * @param $response Response
     * @return mixed
     */
    protected function validateResponse($response)
    {
        $result = xml2array($response->getBody()->getContents());

        if ($result['return_code'] != 'SUCCESS') {
            throw new DomainException($result['return_msg']);
        }

        if (isset($result['sign'])) {
            $this->validateSign($result);
        }
        return $result;
    }

    protected function validateSign($params)
    {
        $sign = $this->generateSign($params);

        if ($sign != $params['sign']) {
            throw new DomainException('签名验证失败');
        }
    }

    public function transfer(Transferable $transfer)
    {
        $params = array_filter([
            'mch_appid'        => $transfer->getExtra('mch_appid'),
            'mchid'            => $this->options['mch_id'],
            'device_info'      => $transfer->getExtra('device_info'),
            'nonce_str'        => Str::random(),
            'partner_trade_no' => $transfer->getAccount(),
            'openid'           => $transfer->getAccount(),
            'check_name'       => $transfer->getRealName() ? 'FORCE_CHECK' : 'NO_CHECK',
            're_user_name'     => $transfer->getRealName(),
            'amount'           => $transfer->getAmount(),
            'desc'             => $transfer->getRemark(),
            'spbill_create_ip' => request()->ip()
        ]);

        $params['sign'] = $this->generateSign($params);

        $xml = array2xml($params);

        $response = Client::post("https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers", Options::makeWithBody($xml)->setExtra([
            'cert'    => $this->options['public_key'],
            'ssl_key' => $this->options['private_key']
        ]));

        $result = $this->validateResponse($response);

        return new TransferResult($result['payment_no'], $result['payment_time']);
    }

    public function query(Payable $charge)
    {
        $params = [
            'appid'        => $this->options['app_id'],
            'mch_id'       => $this->options['mch_id'],
            'nonce_str'    => Str::random(),
            'sign_type'    => 'MD5',
            'out_trade_no' => $charge->getTradeNo()
        ];

        $params['sign'] = $this->generateSign($params);

        $response = Client::post($this->endpoint('pay/orderquery'), Options::makeWithBody(array2xml($params)));
        $result   = $this->validateResponse($response);

        return $result;
    }

    /**
     * 退款
     * @param Refundable $refund
     * @return array
     */
    public function refund(Refundable $refund)
    {
        $params = array_filter([
            'appid'           => $this->options['app_id'],
            'mch_id'          => $this->options['mch_id'],
            'device_info'     => $refund->getExtra('device_info'),
            'nonce_str'       => Str::random(),
            'sign_type'       => 'MD5',
            'out_trade_no'    => $refund->getCharge()->getTradeNo(),
            'out_refund_no'   => $refund->getRefundNo(),
            'total_fee'       => $refund->getCharge()->getAmount(),
            'refund_fee'      => $refund->getAmount(),
            'refund_fee_type' => $refund->getExtra('refund_fee_type'),
            'refund_account'  => $refund->getExtra('refund_account'),
            'op_user_id'      => $refund->getExtra('op_user_id') ?: $this->options['mch_id']
        ]);

        $params['sign'] = $this->generateSign($params);

        $xml = array2xml($params);

        $response = Client::post($this->endpoint('secapi/pay/refund'), Options::makeWithBody($xml)->setExtra([
            'cert'    => $this->options['public_key'],
            'ssl_key' => $this->options['private_key']
        ]));

        $result = $this->validateResponse($response);

        return $result;
    }

    public function refundQuery(Refundable $refund)
    {
        $params = [
            'appid'         => $this->options['app_id'],
            'mch_id'        => $this->options['mch_id'],
            'device_info'   => $refund->getExtra('device_info'),
            'nonce_str'     => Str::random(),
            'sign_type'     => 'MD5',
            'out_refund_no' => $refund->getRefundNo()
        ];

        $params['sign'] = $this->generateSign($params);

        $xml = array2xml($params);

        $response = Client::post($this->endpoint('pay/refundquery'), Options::makeWithBody($xml));

        $result = $this->validateResponse($response);

        return $result;
    }

    public function completePurchase(Request $request)
    {
        libxml_disable_entity_loader(true);
        $data = xml2array($request->getContent());
        $this->validateSign($data);
        $charge = $this->retrieveCharge($data['out_trade_no']);
        if (!$charge->isComplete()) {
            $charge->onComplete(new PurchaseResult('wechat', $data['transaction_id'], $data['total_fee'], $data['result_code'] == 'SUCCESS', Date::parse($data['time_end']), $data));
        }
        $return = [
            'return_code' => 'SUCCESS',
            'return_msg'  => 'OK'
        ];
        return response(array2xml($return));
    }

    public function buildAppParams(Payable $charge)
    {
        $result       = $this->unifiedOrder($charge, Wechat::TYPE_APP);
        $data         = [
            'appid'     => $this->options['app_id'],
            'partnerid' => $this->options['mch_id'],
            'prepayid'  => $result['prepay_id'],
            'package'   => 'Sign=WXPay',
            'noncestr'  => Str::random(),
            'timestamp' => time(),
        ];
        $data['sign'] = $this->generateSign($data);
        return $data;
    }

    public function buildXiaoParams(Payable $charge)
    {
        $result       = $this->unifiedOrder($charge, Wechat::TYPE_JSAPI);
        $data         = [
            'appId'     => $this->options['app_id'],
            'timeStamp' => time(),
            'nonceStr'  => Str::random(),
            'package'   => "prepay_id={$result['prepay_id']}",
            'signType'  => 'MD5'
        ];
        $data['paySign'] = $this->generateSign($data);
        return $data;
    }

    /**
     * 统一下单
     * @param Payable $charge
     * @param string  $type
     * @return array
     * @throws
     */
    public function unifiedOrder(Payable $charge, $type)
    {
        $params = array_filter([
            'appid'            => $this->options['app_id'],
            'mch_id'           => $this->options['mch_id'],
            'device_info'      => $charge->getExtra('device_info'),
            'sign_type'        => 'MD5',
            'attach'           => $charge->getExtra('attach'),
            'fee_type'         => $charge->getExtra('fee_type'),
            'time_start'       => $charge->getExtra('time_start'),
            'time_expire'      => $charge->getExpire(function (Date $date) {
                return $date->format('yyyyMMddHHmmss');
            }),
            'nonce_str'        => Str::random(),
            'body'             => $charge->getSubject(),
            'detail'           => $charge->getBody(),
            'out_trade_no'     => $charge->getTradeNo(),
            'total_fee'        => $charge->getAmount(),
            'spbill_create_ip' => request()->ip(),
            'goods_tag'        => $charge->getExtra('goods_tag'),
            'trade_type'       => $type,
            'notify_url'       => $this->notifyUrl,
            'product_id'       => $charge->getExtra('product_id'),
            'limit_pay'        => $charge->getExtra('limit_pay'),
            'openid'           => $charge->getExtra('openid')
        ]);

        $params['sign'] = $this->generateSign($params);

        $xml = array2xml($params);

        $response = Client::post($this->endpoint('pay/unifiedorder'), Options::makeWithBody($xml));

        $result = $this->validateResponse($response);

        if ($result['result_code'] != 'SUCCESS') {
            throw new DomainException($result['err_code_des']);
        }

        return $result;
    }

    public function buildWapParams(Payable $charge)
    {
        $result           = $this->unifiedOrder($charge, self::TYPE_JSAPI);
        $data             = [
            'appId'     => $this->options['app_id'],
            'package'   => 'prepay_id=' . $result['prepay_id'],
            'nonceStr'  => Str::random(),
            'timeStamp' => (string) time(),
        ];
        $data['signType'] = 'MD5';
        $data['paySign']  = $this->generateSign($data);
        return $data;
    }

}