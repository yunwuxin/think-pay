<?php

namespace yunwuxin\pay\request\wechat;

use think\helper\Str;
use yunwuxin\pay\interfaces\Payable;

class OrderQueryRequest extends Request
{
    protected $uri = 'pay/orderquery';

    public function __invoke(Payable $payable)
    {
        $this->params = [
            'appid'        => $this->channel->getOption('app_id'),
            'mch_id'       => $this->channel->getOption('mch_id'),
            'nonce_str'    => Str::random(),
            'sign_type'    => 'MD5',
            'out_trade_no' => $payable->getTradeNo(),
        ];
    }
}
