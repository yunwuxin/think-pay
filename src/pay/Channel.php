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
use think\Config;
use think\helper\Str;
use think\Request;
use yunwuxin\pay\interfaces\Payable;

abstract class Channel
{
    protected $liveEndpoint;
    protected $testEndpoint;

    protected $test = false;

    protected $notifyUrl;

    public function gateway($name)
    {
        $channel   = class_basename($this);
        $className = "\\yunwuxin\\pay\\gateway\\" . Str::camel($channel) . "\\" . Str::studly($name);
        if (class_exists($className)) {
            /** @var Gateway $gateway */
            $gateway = new $className($this);

            return $gateway;
        }
        throw new InvalidArgumentException("Gateway [{$name}] not supported.");
    }

    public function setTest()
    {
        $this->test = true;
        return $this;
    }

    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
        return $this;
    }

    protected function endpoint()
    {
        if ($this->test) {
            return $this->testEndpoint;
        } else {
            return $this->liveEndpoint;
        }
    }

    /**
     * 退款
     */
    public function refund()
    {
        //todo
    }

    public function refundQuery()
    {
        //todo
    }

    /**
     * 转账
     */
    public function transfer()
    {
        //todo
    }

    /**
     * 查询
     * @param      $tradeNo
     * @param bool $isOut
     * @return mixed
     */
    abstract public function query($tradeNo, $isOut = true);

    abstract public function completePurchase(Request $request);

    /**
     * @param $tradeNo
     * @return Payable
     */
    protected function retrieveCharge($tradeNo)
    {
        $charge = Config::get('pay.charge');

        return $charge::retrieveByTradeNo($tradeNo);
    }

}