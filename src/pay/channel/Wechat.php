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
use think\Cache;
use think\helper\Str;
use think\Request;
use yunwuxin\pay\Channel;
use yunwuxin\pay\entity\PurchaseResult;
use yunwuxin\pay\exception\ConfigException;
use yunwuxin\pay\exception\SignException;
use yunwuxin\pay\http\Client;
use yunwuxin\pay\http\Options;
use yunwuxin\pay\interfaces\Payable;

class Wechat extends Channel
{
    const TYPE_NATIVE = 'NATIVE';
    const TYPE_JSAPI  = 'JSAPI';
    const TYPE_APP    = 'APP';

    protected $liveEndpoint = 'https://api.mch.weixin.qq.com/pay';
    protected $testEndpoint = 'https://api.mch.weixin.qq.com/sandboxnew/pay';

    protected $appId;
    protected $mchId;
    protected $key;

    public function __construct($config)
    {
        if (empty($config['app_id']) || empty($config['mch_id']) || empty($config['key'])) {
            throw new ConfigException;
        }
        $this->appId = $config['app_id'];
        $this->mchId = $config['mch_id'];
        $this->key   = $config['key'];
    }

    public function setTest()
    {
        parent::setTest();
        $this->key = $this->getSignKey();
    }

    public function query($tradeNo, $isOut = true)
    {
        $params = [
            'appid'     => $this->appId,
            'mch_id'    => $this->mchId,
            'nonce_str' => Str::random(),
            'sign_type' => 'MD5'
        ];
        if ($isOut) {
            $params['out_trade_no'] = $tradeNo;
        } else {
            $params['transaction_id'] = $tradeNo;
        }

        $params['sign'] = $this->generateSign($params);

        $response = Client::post($this->endpoint('orderquery'), Options::makeWithBody(array2xml($params)));
        $result   = $this->validateResponse($response);

        return $result;
    }

    public function completePurchase(Request $request)
    {
        $data = xml2array($request->getContent());
        $this->validateSign($data);
        $charge = $this->retrieveCharge($data['out_trade_no']);
        if (!$charge->isComplete()) {
            $charge->onComplete(PurchaseResult::makeByWechat($data));
        }
        $return = [
            'return_code' => 'SUCCESS',
            'return_msg'  => 'OK'
        ];
        return response(array2xml($return));
    }

    protected function generateSign($params)
    {
        unset($params['sign']);
        ksort($params);
        $query = urldecode(http_build_query($params));
        $query .= "&key={$this->key}";
        return strtoupper(md5($query));
    }

    protected function getSignKey()
    {
        return Cache::remember('wechat_sandbox_key', function () {
            $params         = [
                'mch_id'    => $this->mchId,
                'nonce_str' => Str::random()
            ];
            $params['sign'] = $this->generateSign($params);

            $response = Client::post($this->endpoint('getsignkey'), Options::makeWithBody(array2xml($params)));

            $result = $this->validateResponse($response);

            return $result['sandbox_signkey'];
        });
    }

    public function buildAppParams(Payable $charge)
    {
        $result       = $this->unifiedOrder($charge, Wechat::TYPE_NATIVE);
        $data         = [
            'appid'     => $this->appId,
            'partnerid' => $this->mchId,
            'prepayid'  => $result['prepay_id'],
            'package'   => 'Sign=WXPay',
            'noncestr'  => Str::random(),
            'timestamp' => time(),
        ];
        $data['sign'] = $this->generateSign($data);
        return $data;
    }

    public function buildWapParams(Payable $charge)
    {
        $result           = $this->unifiedOrder($charge, self::TYPE_JSAPI);
        $data             = [
            'appId'     => $this->appId,
            'package'   => 'prepay_id=' . $result['prepay_id'],
            'nonceStr'  => Str::random(),
            'timeStamp' => time(),
        ];
        $data['signType'] = 'MD5';
        $data['paySign']  = $this->generateSign($data);
        return $data;
    }

    /**
     * 统一下单
     * @param Payable $charge
     * @param string  $type
     * @return array
     * @throws SignException
     */
    public function unifiedOrder(Payable $charge, $type)
    {
        $params = array_filter([
            'appid'            => $this->appId,
            'mch_id'           => $this->mchId,
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

        $response = Client::post($this->endpoint('unifiedorder'), Options::makeWithBody($xml));

        $result = $this->validateResponse($response);

        return $result;
    }

    protected function validateSign($params)
    {
        $sign = $this->generateSign($params);

        if ($sign != $params['sign']) {
            throw new SignException;
        }
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

    protected function endpoint($uri = '')
    {
        if ($this->test) {
            return $this->testEndpoint . '/' . $uri;
        } else {
            return $this->liveEndpoint . '/' . $uri;
        }
    }

}