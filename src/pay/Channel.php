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

use InvalidArgumentException;

abstract class Channel
{

    protected static $gateways = [];

    protected function buildGateway($name)
    {
        if (isset(static::$gateways[$name])) {
            return new static::$gateways[$name]($this);
        }
        throw new InvalidArgumentException("Gateway [{$name}] not supported.");
    }

    /**
     * 设置支付网关
     * @param $name
     * @return Gateway
     */
    public function useGateway($name)
    {
        return $this->buildGateway($name);
    }

    /**
     * 退款
     */
    public function refund()
    {

    }

    /**
     * 打款
     */
    public function pay()
    {

    }

}