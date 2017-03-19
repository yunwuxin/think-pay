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

use function http_build_query;
use yunwuxin\pay\gateway\Alipay;
use yunwuxin\pay\interfaces\Payable;

class App extends Alipay
{

    /**
     * 购买
     * @param Payable $charge
     * @return mixed
     */
    public function pay(Payable $charge)
    {
        return http_build_query($this->buildParams($charge));
    }
}