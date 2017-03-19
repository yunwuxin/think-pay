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
 * 手机网站支付网关(新版)
 * Class Wap
 * @package yunwuxin\pay\channel\alipay\gateway
 */
class Wap extends Alipay
{

    /**
     * 购买
     * @param Payable $charge
     * @return mixed
     */
    public function pay(Payable $charge)
    {
        // TODO: Implement pay() method.
    }
}