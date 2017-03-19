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

class QrCode extends Alipay
{

    /**
     * 购买
     * @param Payable $charge
     * @return mixed
     */
    public function pay(Payable $charge)
    {
        $result = $this->preCreate($charge);
        return $result['qr_code'];
    }
}