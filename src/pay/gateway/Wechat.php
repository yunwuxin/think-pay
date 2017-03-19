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

namespace yunwuxin\pay\gateway;

use Jenssegers\Date\Date;
use think\helper\Str;
use yunwuxin\pay\exception\SignException;
use yunwuxin\pay\exception\wechat\UnifiedOrderException;
use yunwuxin\pay\Gateway;
use yunwuxin\pay\http\Client;
use yunwuxin\pay\http\Options;
use yunwuxin\pay\interfaces\Payable;

abstract class Wechat extends Gateway
{
    const TYPE_APP    = 'APP';
    const TYPE_NATIVE = 'NATIVE';
    const TYPE_JSAPI  = 'JSAPI';

    /** @var \yunwuxin\pay\channel\Wechat */
    protected $channel;

    protected $endpoint = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    protected function generateSign($params)
    {
        unset($params['sign']);
        ksort($params);
        $query = urldecode(http_build_query($params));
        $query .= "&key={$this->channel->getKey()}";
        return strtoupper(md5($query));
    }

    /**
     * 统一下单
     * @param Payable $charge
     * @param string  $type
     * @return array
     * @throws SignException
     * @throws UnifiedOrderException
     */
    protected function unifiedOrder(Payable $charge, $type)
    {
        $params = array_filter([
            'appid'            => $this->channel->getAppId(),
            'mch_id'           => $this->channel->getMchId(),
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
            'out_trade_no'     => $charge->getOrderNo(),
            'total_fee'        => $charge->getAmount(),
            'spbill_create_ip' => request()->ip(),
            'goods_tag'        => $charge->getExtra('goods_tag'),
            'trade_type'       => $type,
            'notify_url'       => 'aa',
            'product_id'       => $charge->getExtra('product_id'),
            'limit_pay'        => $charge->getExtra('limit_pay'),
            'openid'           => $charge->getExtra('openid')
        ]);

        $params['sign'] = $this->generateSign($params);

        $xml = array2xml($params);

        $response = Client::post($this->endpoint, Options::makeWithBody($xml));

        $result = xml2array($response->getBody()->getContents());

        if ($result['return_code'] != 'SUCCESS') {
            throw new UnifiedOrderException($result['return_msg']);
        }

        $sign = $this->generateSign($result);

        if ($sign != $result['sign']) {
            throw new SignException;
        }

        return $result;
    }

}