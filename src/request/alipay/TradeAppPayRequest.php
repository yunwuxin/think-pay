<?php

namespace yunwuxin\pay\request\alipay;

use yunwuxin\pay\interfaces\Payable;

class TradeAppPayRequest extends Request
{
    protected $method = 'alipay.trade.app.pay';

    public function __construct(Payable $payable)
    {
        $this->bizContent = [
            'body'                 => $payable->getBody(),
            'subject'              => $payable->getSubject(),
            'out_trade_no'         => $payable->getTradeNo(),
            'total_amount'         => $payable->getAmount() / 100,
            'seller_id'            => $payable->getExtra('seller_id'),
            'product_code'         => 'QUICK_MSECURITY_PAY',
            'goods_type'           => $payable->getExtra('goods_type'),
            'passback_params'      => $payable->getExtra('passback_params'),
            'promo_params'         => $payable->getExtra('promo_params'),
            'extend_params'        => $payable->getExtra('extend_params'),
            'enable_pay_channels'  => $payable->getExtra('enable_pay_channels'),
            'disable_pay_channels' => $payable->getExtra('disable_pay_channels'),
            'store_id'             => $payable->getExtra('store_id'),
        ];

        $this->params['notify_url'] = $this->channel->getNotifyUrl();
    }
}
