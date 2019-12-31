<?php
/**
 * Created by PhpStorm.
 * User: yunwuxin
 * Date: 2018/7/6
 * Time: 15:30
 */

namespace yunwuxin\pay\gateway\wechat;

use think\helper\Str;
use yunwuxin\pay\channel\Wechat;
use yunwuxin\pay\entity\ParamResult;
use yunwuxin\pay\Gateway;
use yunwuxin\pay\interfaces\Payable;
use yunwuxin\pay\request\wechat\UnifiedOrderRequest;

class Xiao extends Gateway
{

    /**
     * 付款
     * @param Payable $charge
     * @return ParamResult
     */
    public function purchase(Payable $charge)
    {
        $request = $this->channel->createRequest(UnifiedOrderRequest::class, $charge, Wechat::TYPE_JSAPI);

        $result          = $this->channel->sendRequest($request);
        $data            = [
            'appId'     => $this->channel->getOption('app_id'),
            'timeStamp' => (string) time(),
            'nonceStr'  => Str::random(),
            'package'   => "prepay_id={$result['prepay_id']}",
            'signType'  => 'MD5',
        ];
        $data['paySign'] = $this->channel->generateSign($data);
        return new ParamResult($data);
    }
}
