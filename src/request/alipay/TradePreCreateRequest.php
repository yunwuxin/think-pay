<?php

namespace yunwuxin\pay\request\alipay;

use yunwuxin\pay\interfaces\Payable;

class TradePreCreateRequest extends Request
{
    protected $method = 'alipay.trade.precreate';

    public function __construct(Payable $payable)
    {
        $this->bizContent = [
            'out_trade_no'          => $payable->getTradeNo(),
            'seller_id'             => $payable->getExtra('seller_id'),
            'total_amount'          => $payable->getAmount() / 100,
            'discountable_amount'   => $payable->getExtra('discountable_amount'),
            'undiscountable_amount' => $payable->getExtra('undiscountable_amount'),
            'buyer_logon_id'        => $payable->getExtra('buyer_logon_id'),
            'subject'               => $payable->getSubject(),
            'body'                  => $payable->getBody(),
            'goods_detail'          => $payable->getExtra('goods_detail'),
            'operator_id'           => $payable->getExtra('operator_id'),
            'store_id'              => $payable->getExtra('store_id'),
            'terminal_id'           => $payable->getExtra('terminal_id'),
            'extend_params'         => $payable->getExtra('extend_params'),
            'royalty_info'          => $payable->getExtra('royalty_info'),
            'sub_merchant'          => $payable->getExtra('sub_merchant'),
            'alipay_store_id'       => $payable->getExtra('alipay_store_id'),
        ];

        $this->params['notify_url'] = $this->channel->getNotifyUrl();
    }
}
