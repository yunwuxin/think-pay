<?php

namespace yunwuxin\pay\request\wechat;

use think\helper\Str;
use yunwuxin\pay\interfaces\Refundable;

class RefundQueryRequest extends Request
{
    protected $uri = 'pay/refundquery';

    public function __invoke(Refundable $refund)
    {
        $this->params = [
            'appid'         => $this->channel->getOption('app_id'),
            'mch_id'        => $this->channel->getOption('mch_id'),
            'device_info'   => $refund->getExtra('device_info'),
            'nonce_str'     => Str::random(),
            'sign_type'     => 'MD5',
            'out_refund_no' => $refund->getRefundNo(),
        ];
    }
}
