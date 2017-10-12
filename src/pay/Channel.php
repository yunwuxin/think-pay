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
use yunwuxin\pay\interfaces\Refundable;
use yunwuxin\pay\interfaces\Transferable;

abstract class Channel
{

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

    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
        return $this;
    }

    abstract public function setTest();

    /**
     * 退款
     * @param Refundable $refund
     */
    abstract public function refund(Refundable $refund);

    /**
     * 退款查询
     * @param Refundable $refund
     * @return mixed
     */
    abstract public function refundQuery(Refundable $refund);

    /**
     * 转账
     * @param Transferable $transfer
     * @return
     */
    abstract public function transfer(Transferable $transfer);

    /**
     * 查询
     * @param Payable $charge
     * @return mixed
     */
    abstract public function query(Payable $charge);

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