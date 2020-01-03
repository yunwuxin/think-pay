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

use think\helper\Str;
use yunwuxin\pay\channel\Wechat;
use yunwuxin\pay\entity\PurchaseResponse;
use yunwuxin\pay\Gateway;
use yunwuxin\pay\interfaces\Payable;
use yunwuxin\pay\request\wechat\UnifiedOrderRequest;

class Js extends Gateway
{

    /**
     * 购买
     * @param Payable $charge
     * @return PurchaseResponse
     */
    public function purchase(Payable $charge)
    {
        $request = $this->channel->createRequest(UnifiedOrderRequest::class, $charge, Wechat::TYPE_JSAPI);

        $result = $this->channel->sendRequest($request);

        $data = [
            'appId'     => $this->channel->getOption('app_id'),
            'timeStamp' => (string) time(),
            'nonceStr'  => Str::random(),
            'package'   => "prepay_id={$result['prepay_id']}",
            'signType'  => 'MD5',
        ];

        $data['paySign'] = $this->channel->generateSign($data);

        return new PurchaseResponse($data, PurchaseResponse::TYPE_PARAMS);
    }
}
