<?php

namespace yunwuxin\pay\request\alipay;

use yunwuxin\pay\interfaces\Payable;

class TradeQueryRequest extends Request
{
    protected $method = 'alipay.trade.query';

    public function __invoke(Payable $payable)
    {
        $this->bizContent = [
            'out_trade_no' => $payable->getTradeNo(),
        ];
    }
}
