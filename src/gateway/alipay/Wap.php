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

namespace yunwuxin\pay\gateway\alipay;

use yunwuxin\pay\entity\ResponseResult;
use yunwuxin\pay\Gateway;
use yunwuxin\pay\interfaces\Payable;
use yunwuxin\pay\request\alipay\TradeWapPayRequest;

/**
 * 手机网站支付网关
 * Class Wap
 * @package yunwuxin\pay\channel\alipay\gateway
 */
class Wap extends Gateway
{

    /**
     * 购买
     * @param Payable $charge
     * @return mixed
     */
    public function purchase(Payable $charge)
    {
        $request = $this->channel->createRequest(TradeWapPayRequest::class, $charge);

        $url = sprintf('%s?%s', $request->getUri(), http_build_query($request->getBody()));

        $response = redirect($url);
        return new ResponseResult($response);
    }
}
