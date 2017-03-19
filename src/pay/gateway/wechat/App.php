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

class App extends Wechat
{

    /**
     * 购买
     * @param Payable $charge
     * @return mixed
     */
    public function pay(Payable $charge)
    {
        $result       = $this->unifiedOrder($charge, self::TYPE_NATIVE);
        $data         = [
            'appid'     => $this->channel->getAppId(),
            'partnerid' => $this->channel->getMchId(),
            'prepayid'  => $result['prepay_id'],
            'package'   => 'Sign=WXPay',
            'noncestr'  => Str::random(),
            'timestamp' => time(),
        ];
        $data['sign'] = $this->generateSign($data);
        return $data;
    }
}