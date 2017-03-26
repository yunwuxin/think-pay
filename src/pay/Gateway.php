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

namespace yunwuxin\pay;

use yunwuxin\pay\channel\Alipay;
use yunwuxin\pay\channel\Wechat;
use yunwuxin\pay\interfaces\Payable;

abstract class Gateway
{

    /** @var Alipay|Wechat */
    protected $channel;

    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * 付款
     * @param Payable $charge
     */
    abstract public function purchase(Payable $charge);

}