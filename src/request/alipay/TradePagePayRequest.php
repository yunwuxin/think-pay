<?php

namespace yunwuxin\pay\request\alipay;

use yunwuxin\pay\interfaces\Payable;

class TradePagePayRequest extends Request
{
    protected $method = 'alipay.trade.page.pay';

    public function __invoke(Payable $payable)
    {
        $this->bizContent = [
            'body'                 => $payable->getBody(),
            'subject'              => $payable->getSubject(),
            'out_trade_no'         => $payable->getTradeNo(),
            'total_amount'         => $payable->getAmount() / 100,
            'product_code'         => 'FAST_INSTANT_TRADE_PAY',
            'goods_type'           => $payable->getExtra('goods_type'),
            'passback_params'      => $payable->getExtra('passback_params'),
            'promo_params'         => $payable->getExtra('promo_params'),
            'extend_params'        => $payable->getExtra('extend_params'),
            'enable_pay_channels'  => $payable->getExtra('enable_pay_channels'),
            'disable_pay_channels' => $payable->getExtra('disable_pay_channels'),
            'store_id'             => $payable->getExtra('store_id'),
            'qr_pay_mode'          => $payable->getExtra('qr_pay_mode'),
            'qrcode_width'         => $payable->getExtra('qrcode_width'),
            'request_from_url'     => $payable->getExtra('request_from_url'),
            'integration_type'     => $payable->getExtra('integration_type'),
        ];

        $this->params['notify_url'] = $this->channel->getNotifyUrl();
        $this->params['return_url'] = $payable->getReturnUrl();
    }
}
