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

use yunwuxin\pay\entity\QrCodeResult;
use yunwuxin\pay\Gateway;
use yunwuxin\pay\interfaces\Payable;

class QrCode extends Gateway
{

    /**
     * 购买
     * @param Payable $charge
     * @return mixed
     */
    public function purchase(Payable $charge)
    {
        $result = $this->channel->preCreate($charge);
        return new QrCodeResult($result['qr_code']);
    }
}