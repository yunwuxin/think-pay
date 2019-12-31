<?php

namespace yunwuxin\pay\request\wechat;

use Carbon\Carbon;
use think\helper\Str;
use yunwuxin\pay\interfaces\Payable;

class UnifiedOrderRequest extends Request
{
    protected $uri = 'pay/unifiedorder';

    public function __construct(Payable $charge, $type)
    {
        $this->params = array_filter([
            'appid'            => $this->channel->getOption('app_id'),
            'mch_id'           => $this->channel->getOption('mch_id'),
            'device_info'      => $charge->getExtra('device_info'),
            'sign_type'        => 'MD5',
            'attach'           => $charge->getExtra('attach'),
            'fee_type'         => $charge->getExtra('fee_type'),
            'time_start'       => $charge->getExtra('time_start'),
            'time_expire'      => $charge->getExpire(function (Carbon $date) {
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
            'notify_url'       => $this->channel->getNotifyUrl(),
            'product_id'       => $charge->getExtra('product_id'),
            'limit_pay'        => $charge->getExtra('limit_pay'),
            'openid'           => $charge->getExtra('openid'),
        ]);
    }
}
