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

use yunwuxin\pay\gateway\Wechat;
use yunwuxin\pay\interfaces\Payable;

class PubQrCode extends Wechat
{

    /**
     * 购买
     * @param Payable $charge
     * @return mixed
     */
    public function pay(Payable $charge)
    {
        $result = $this->unifiedOrder($charge, self::TYPE_NATIVE);
        return $result['code_url'];
    }
}