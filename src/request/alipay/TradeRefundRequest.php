<?php

namespace yunwuxin\pay\request\alipay;

use yunwuxin\pay\interfaces\Refundable;

class TradeRefundRequest extends Request
{
    protected $method = 'alipay.trade.refund';

    public function __invoke(Refundable $refund)
    {
        $this->bizContent = [
            'out_trade_no'   => $refund->getCharge()->getTradeNo(),
            'refund_amount'  => $refund->getAmount() / 100,
            'refund_reason'  => $refund->getExtra('refund_reason'),
            'out_request_no' => $refund->getRefundNo(),
            'operator_id'    => $refund->getExtra('operator_id'),
            'store_id'       => $refund->getExtra('store_id'),
            'terminal_id'    => $refund->getExtra('terminal_id'),
        ];
    }
}
