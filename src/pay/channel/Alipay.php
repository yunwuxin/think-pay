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
use think\Request;
use yunwuxin\pay\Channel;
use yunwuxin\pay\entity\PurchaseResult;
use yunwuxin\pay\exception\ConfigException;
use yunwuxin\pay\exception\SignException;
use yunwuxin\pay\http\Client;
use yunwuxin\pay\http\Options;
use yunwuxin\pay\interfaces\Payable;
use yunwuxin\pay\interfaces\Refundable;

class Alipay extends Channel
{
    protected $liveEndpoint = "https://openapi.alipay.com/gateway.do";
    protected $testEndpoint = "https://openapi.alipaydev.com/gateway.do";

    protected $appId;
    protected $publicKey;
    protected $privateKey;
    protected $signType = 'RSA2';

    public function __construct($config)
    {
        if (empty($config['app_id']) || empty($config['public_key']) || empty($config['private_key'])) {
            throw new ConfigException;
        }
        $this->appId      = $config['app_id'];
        $this->publicKey  = $config['public_key'];
        $this->privateKey = $config['private_key'];
        if (!empty($config['sign_type'])) {
            $this->signType = $config['sign_type'];
        }
    }

    /**
     * 订单查询
     * @param Payable $charge
     * @return array
     */
    public function query(Payable $charge)
    {
        $bizContent = [
            'trade_no' => $charge->getTradeNo()
        ];

        $method   = 'alipay.trade.query';
        $params   = $this->buildParams($method, $bizContent);
        $response = Client::get($this->endpoint(), Options::makeWithQuery($params));

        $result = $this->validateResponse($response, $method);

        return $result;
    }

    /**
     * 退款
     * @param Refundable $refund
     * @return array
     */
    public function refund(Refundable $refund)
    {
        $bizContent = array_filter([
            'out_trade_no'   => $refund->getCharge()->getTradeNo(),
            'refund_amount'  => $refund->getAmount() / 100,
            'refund_reason'  => $refund->getExtra('refund_reason'),
            'out_request_no' => $refund->getRefundNo(),
            'operator_id'    => $refund->getExtra('operator_id'),
            'store_id'       => $refund->getExtra('store_id'),
            'terminal_id'    => $refund->getExtra('terminal_id')
        ]);

        $method   = 'alipay.trade.refund';
        $params   = $this->buildParams($method, $bizContent);
        $response = Client::get($this->endpoint(), Options::makeWithQuery($params));

        $result = $this->validateResponse($response, $method);

        return $result;
    }

    public function refundQuery(Refundable $refund)
    {
        $bizContent = [
            'out_trade_no'   => $refund->getCharge()->getTradeNo(),
            'out_request_no' => $refund->getRefundNo()
        ];
        $method     = 'alipay.trade.fastpay.refund.query';
        $params     = $this->buildParams($method, $bizContent);
        $response   = Client::get($this->endpoint(), Options::makeWithQuery($params));

        $result = $this->validateResponse($response, $method);

        return $result;
    }

    public function completePurchase(Request $request)
    {
        $data = $request->post();
        $sign = $data['sign'];

        $this->signType = $data['sign_type'];

        unset($data['sign'], $data['sign_type']);

        if (!$this->verifySign($this->buildSignContent($data), $sign)) {
            throw new SignException;
        }

        $charge = $this->retrieveCharge($data['out_trade_no']);
        if (!$charge->isComplete()) {
            $charge->onComplete(new PurchaseResult('alipay', $data['trade_no'], $data['total_amount'] * 100, 'TRADE_SUCCESS' == $data['trade_status'], Date::parse($data['gmt_payment']), $data));
        }
        return response('success');
    }

    public function buildAppParams(Payable $charge)
    {
        $bizContent = array_filter([
            'body'                 => $charge->getBody(),
            'subject'              => $charge->getSubject(),
            'out_trade_no'         => $charge->getTradeNo(),
            'timeout_express'      => $charge->getExpire(function (Date $date) {
                //todo
            }),
            'total_amount'         => $charge->getAmount() / 100,
            'seller_id'            => $charge->getExtra('seller_id'),
            'product_code'         => 'QUICK_MSECURITY_PAY',
            'goods_type'           => $charge->getExtra('goods_type'),
            'passback_params'      => $charge->getExtra('passback_params'),
            'promo_params'         => $charge->getExtra('promo_params'),
            'extend_params'        => $charge->getExtra('extend_params'),
            'enable_pay_channels'  => $charge->getExtra('enable_pay_channels'),
            'disable_pay_channels' => $charge->getExtra('disable_pay_channels'),
            'store_id'             => $charge->getExtra('store_id')
        ]);

        return $this->buildParams('alipay.trade.app.pay', $bizContent, ['notify_url' => $this->notifyUrl]);
    }

    public function buildPreCreateParams(Payable $charge)
    {
        $bizContent = array_filter([
            'out_trade_no'          => $charge->getTradeNo(),
            'seller_id'             => $charge->getExtra('seller_id'),
            'total_amount'          => $charge->getAmount() / 100,
            'discountable_amount'   => $charge->getExtra('discountable_amount'),
            'undiscountable_amount' => $charge->getExtra('undiscountable_amount'),
            'buyer_logon_id'        => $charge->getExtra('buyer_logon_id'),
            'subject'               => $charge->getSubject(),
            'body'                  => $charge->getBody(),
            'goods_detail'          => $charge->getExtra('goods_detail'),
            'operator_id'           => $charge->getExtra('operator_id'),
            'store_id'              => $charge->getExtra('store_id'),
            'terminal_id'           => $charge->getExtra('terminal_id'),
            'extend_params'         => $charge->getExtra('extend_params'),
            'timeout_express'       => $charge->getExpire(function (Date $date) {
                //todo
            }),
            'royalty_info'          => $charge->getExtra('royalty_info'),
            'sub_merchant'          => $charge->getExtra('sub_merchant'),
            'alipay_store_id'       => $charge->getExtra('alipay_store_id')
        ]);

        return $this->buildParams('alipay.trade.precreate', $bizContent, ['notify_url' => $this->notifyUrl]);
    }

    public function wapPay(Payable $charge)
    {
        $bizContent = array_filter([
            'body'                 => $charge->getBody(),
            'subject'              => $charge->getSubject(),
            'out_trade_no'         => $charge->getTradeNo(),
            'timeout_express'      => $charge->getExpire(function (Date $date) {
                //todo
            }),
            'total_amount'         => $charge->getAmount() / 100,
            'seller_id'            => $charge->getExtra('seller_id'),
            'auth_token'           => $charge->getExtra('auth_token'),
            'product_code'         => 'QUICK_WAP_PAY',
            'goods_type'           => $charge->getExtra('goods_type'),
            'passback_params'      => $charge->getExtra('passback_params'),
            'promo_params'         => $charge->getExtra('promo_params'),
            'extend_params'        => $charge->getExtra('extend_params'),
            'enable_pay_channels'  => $charge->getExtra('enable_pay_channels'),
            'disable_pay_channels' => $charge->getExtra('disable_pay_channels'),
            'store_id'             => $charge->getExtra('store_id')
        ]);

        $params = $this->buildParams('alipay.trade.wap.pay', $bizContent, [
            'notify_url' => $this->notifyUrl,
            'return_url' => $charge->getExtra('return_url')
        ]);

        return sprintf('%s?%s', $this->endpoint(), http_build_query($params));

    }

    public function preCreate(Payable $charge)
    {
        $params = $this->buildPreCreateParams($charge);

        $response = Client::get($this->endpoint(), Options::makeWithQuery($params));

        $result = $this->validateResponse($response, 'alipay.trade.precreate');

        return $result;
    }

    protected function buildParams($method, $bizContent, $extra = [])
    {
        $params = array_merge([
            'app_id'      => $this->appId,
            'method'      => $method,
            'format'      => 'JSON',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'timestamp'   => Date::now()->format('Y-m-d H:i:s'),
            'version'     => '1.0',
            'biz_content' => json_encode($bizContent, JSON_UNESCAPED_UNICODE)
        ], $extra);

        $params['sign'] = $this->generateSign($params);

        return $params;
    }

    /**
     * @param Response $response
     * @param          $method
     * @return array
     * @throws SignException
     */
    protected function validateResponse($response, $method)
    {
        $response = json_decode($response->getBody()->getContents(), true);

        $key    = str_replace('.', '_', $method) . '_response';
        $result = $response[$key];

        if (empty($result['code']) || $result['code'] != 10000) {
            throw new DomainException(isset($result['sub_msg']) ? $result['sub_msg'] : $result['msg']);
        }
        if (isset($response['sign'])) {
            if (!$this->verifySign(json_encode($result, JSON_UNESCAPED_UNICODE), $response['sign'])) {
                throw new SignException;
            }
        }
        return $result;
    }

    protected function verifySign($data, $sign)
    {
        $res = $this->buildPublicKey();
        if ('RSA2' == $this->signType) {
            return (bool) openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        } else {
            return (bool) openssl_verify($data, base64_decode($sign), $res);
        }
    }

    protected function generateSign($params)
    {
        $data = $this->buildSignContent($params);
        $res  = $this->buildPrivateKey();
        if ("RSA2" == $this->signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $res);
        }
        return base64_encode($sign);
    }

    /**
     * 生成待签名内容
     * @param $params
     * @return string
     */
    protected function buildSignContent($params)
    {
        ksort($params);
        return urldecode(http_build_query($params));
    }

    protected function buildPublicKey()
    {
        return "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($this->publicKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
    }

    protected function buildPrivateKey()
    {
        return "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->privateKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
    }

}