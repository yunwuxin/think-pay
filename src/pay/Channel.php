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

use RuntimeException;
use think\Response;

abstract class Channel
{
    protected $gateway;

    /**
     * 创建网关
     * @param $name
     * @return Gateway
     */
    protected function buildGateway($name)
    {

    }

    /**
     * 设置支付网关
     * @param $name
     * @return $this
     */
    public function setGateway($name)
    {
        $this->gateway = $this->buildGateway($name);
        return $this;
    }

    /**
     * 购买
     * @param Order $goods
     * @return Response
     */
    public function purchase(Order $goods)
    {

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

    public function __call($name, $arguments)
    {
        if ($this->gateway && method_exists($this, $name)) {
            call_user_func_array([$this, $name], $arguments);
            return $this;
        }
        throw new RuntimeException("method {$name} not found");
    }
}