<?php

namespace yunwuxin\pay\gateway\wechat;

use yunwuxin\pay\channel\Wechat;
use yunwuxin\pay\entity\PurchaseResponse;
use yunwuxin\pay\Gateway;
use yunwuxin\pay\interfaces\Payable;
use yunwuxin\pay\request\wechat\UnifiedOrderRequest;

class Wap extends Gateway
{

    /**
     * @inheritDoc
     */
    public function purchase(Payable $charge)
    {
        $request = $this->channel->createRequest(UnifiedOrderRequest::class, $charge, Wechat::TYPE_MWEB);

        $result = $this->channel->sendRequest($request);

        return new PurchaseResponse($result['mweb_url'], PurchaseResponse::TYPE_REDIRECT);
    }
}
