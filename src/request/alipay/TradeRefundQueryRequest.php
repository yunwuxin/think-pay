<?php

namespace yunwuxin\pay\request\alipay;

use yunwuxin\pay\interfaces\Refundable;

class TradeRefundQueryRequest extends Request
{
    protected $method = 'alipay.trade.fastpay.refund.query';

    public function __invoke(Refundable $refund)
    {
        $this->bizContent = [
            'out_trade_no'   => $refund->getCharge()->getTradeNo(),
            'out_request_no' => $refund->getRefundNo(),
        ];
    }
}
