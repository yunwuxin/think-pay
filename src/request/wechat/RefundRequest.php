<?php

namespace yunwuxin\pay\request\wechat;

use think\helper\Str;
use yunwuxin\pay\interfaces\Refundable;

class RefundRequest extends Request
{
    protected $uri = 'secapi/pay/refund';

    public function __construct(Refundable $refund)
    {
        $this->params = array_filter([
            'appid'           => $this->channel->getOption('app_id'),
            'mch_id'          => $this->channel->getOption('mch_id'),
            'device_info'     => $refund->getExtra('device_info'),
            'nonce_str'       => Str::random(),
            'sign_type'       => 'MD5',
            'out_trade_no'    => $refund->getCharge()->getTradeNo(),
            'out_refund_no'   => $refund->getRefundNo(),
            'total_fee'       => $refund->getCharge()->getAmount(),
            'refund_fee'      => $refund->getAmount(),
            'refund_fee_type' => $refund->getExtra('refund_fee_type'),
            'refund_account'  => $refund->getExtra('refund_account'),
            'op_user_id'      => $refund->getExtra('op_user_id') ?: $this->channel->getOption('mch_id'),
        ]);
    }
}
