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

use yunwuxin\pay\gateway\Alipay;
use yunwuxin\pay\interfaces\Payable;

/**
 * 即时到账网关
 * Class Express
 * @package yunwuxin\pay\channel\alipay\gateway
 */
class LegacyExpress extends Alipay
{

    protected $endpoint = "https://mapi.alipay.com/gateway.do";

    /**
     * 购买
     * @param Payable $charge
     * @return mixed
     */
    public function pay(Payable $charge)
    {
        $params = array_filter([
            'service' => 'create_direct_pay_by_user',
            'partner'=>''
        ]);
    }
}