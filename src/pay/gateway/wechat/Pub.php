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
use yunwuxin\pay\gateway\Wechat;
use yunwuxin\pay\interfaces\Payable;

class Pub extends Wechat
{

    /**
     * 购买
     * @param Payable $charge
     * @return mixed
     */
    public function pay(Payable $charge)
    {
        $result           = $this->unifiedOrder($charge, self::TYPE_JSAPI);
        $data             = [
            'appId'     => $this->channel->getAppId(),
            'package'   => 'prepay_id=' . $result['prepay_id'],
            'nonceStr'  => Str::random(),
            'timeStamp' => time(),
        ];
        $data['signType'] = 'MD5';
        $data['paySign']  = $this->generateSign($data);
        return $data;
    }
}