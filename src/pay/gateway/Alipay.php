<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace yunwuxin\pay\gateway;

use Jenssegers\Date\Date;
use yunwuxin\pay\exception\alipay\PreCreateException;
use yunwuxin\pay\exception\SignException;
use yunwuxin\pay\Gateway;
use yunwuxin\pay\http\Client;
use yunwuxin\pay\http\Options;
use yunwuxin\pay\interfaces\Payable;

abstract class Alipay extends Gateway
{
    /** @var \yunwuxin\pay\channel\Alipay */
    protected $channel;

    protected $endpoint = "https://openapi.alipay.com/gateway.do";

    protected function buildParams(Payable $charge)
    {
        $bizContent = array_filter([
            'out_trade_no'          => $charge->getOrderNo(),
            'seller_id'             => $this->channel->getSellerId(),
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

        $params = array_filter([
            'app_id'         => $this->channel->getAppId(),
            'method'         => 'alipay.trade.precreate',
            'format'         => 'JSON',
            'charset'        => 'utf-8',
            'sign_type'      => 'RSA2',
            'timestamp'      => Date::now()->format('Y-m-d H:i:s'),
            'version'        => '1.0',
            'notify_url'     => 'notify',
            'app_auth_token' => $charge->getExtra('app_auth_token'),
            'biz_content'    => json_encode($bizContent, JSON_UNESCAPED_UNICODE)
        ]);

        $params['sign'] = $this->generateSign($params);
        return $params;
    }

    protected function preCreate(Payable $charge)
    {
        $params = $this->buildParams($charge);

        $response = Client::get($this->endpoint, Options::makeWithQuery($params));

        $response = json_decode($response->getBody()->getContents(), true);

        $result = $response['alipay_trade_precreate_response'];

        if (!$this->verifySign(json_encode($result, JSON_UNESCAPED_UNICODE), $response['sign'])) {
            throw new SignException;
        }

        if (!empty($result['code']) && $result['code'] == 1000) {
            return $result;
        }

        throw new PreCreateException($result['msg']);
    }

    protected function verifySign($data, $sign)
    {
        return (bool) openssl_verify($data, base64_decode($sign), $this->getPublicKey(), OPENSSL_ALGO_SHA256);
    }

    protected function generateSign($params)
    {
        openssl_sign($this->getSignContent($params), $sign, $this->getPrivateKey(), OPENSSL_ALGO_SHA256);
        return base64_encode($sign);
    }

    protected function getSignContent($params)
    {
        unset($params['sign']);
        ksort($params);
        return urldecode(http_build_query($params));
    }

    protected function getPublicKey()
    {
        return "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($this->channel->getPublicKey(), 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
    }

    protected function getPrivateKey()
    {
        return "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->channel->getPrivateKey(), 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
    }
}