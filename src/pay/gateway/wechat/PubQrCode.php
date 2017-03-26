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
use yunwuxin\pay\Gateway;
use yunwuxin\pay\interfaces\Payable;

class PubQrCode extends Gateway
{

    /**
     * 购买
     * @param Payable $charge
     * @return mixed
     */
    public function purchase(Payable $charge)
    {
        $result = $this->channel->unifiedOrder($charge, Wechat::TYPE_NATIVE);
        return $result['code_url'];
    }
}