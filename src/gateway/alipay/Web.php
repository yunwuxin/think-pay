<?php

namespace yunwuxin\pay\gateway\alipay;

use yunwuxin\pay\entity\PurchaseResponse;
use yunwuxin\pay\Gateway;
use yunwuxin\pay\interfaces\Payable;
use yunwuxin\pay\request\alipay\TradePagePayRequest;

class Web extends Gateway
{

    /**
     * @inheritDoc
     */
    public function purchase(Payable $charge)
    {
        $request = $this->channel->createRequest(TradePagePayRequest::class, $charge);

        return new PurchaseResponse($request->getUri(), PurchaseResponse::TYPE_REDIRECT);
    }
}
