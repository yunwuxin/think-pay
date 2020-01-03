<?php
// +----------------------------------------------------------------------
// | ThinkPay
// +----------------------------------------------------------------------
// | Copyright (c) yunwuxin All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace yunwuxin\pay\gateway\wechat;

use yunwuxin\pay\channel\Wechat;
use yunwuxin\pay\entity\PurchaseResponse;
use yunwuxin\pay\Gateway;
use yunwuxin\pay\interfaces\Payable;
use yunwuxin\pay\request\wechat\UnifiedOrderRequest;

class Native extends Gateway
{

    /**
     * 购买
     * @param Payable $charge
     * @return PurchaseResponse
     */
    public function purchase(Payable $charge)
    {
        $request = $this->channel->createRequest(UnifiedOrderRequest::class, $charge, Wechat::TYPE_NATIVE);

        $result = $this->channel->sendRequest($request);

        return new PurchaseResponse($result['code_url'], PurchaseResponse::TYPE_SCAN);
    }
}
