<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
namespace yunwuxin\pay\entity;

use Jenssegers\Date\Date;

class PurchaseResult
{
    protected $raw;

    protected $channel;

    protected $tradeNo;

    protected $amount;

    protected $isPaid;

    protected $payTime;

    public function __construct($channel, $tradeNo, $amount, $isPaid, $payTime, $raw)
    {
        $this->channel = $channel;
        $this->tradeNo = $tradeNo;
        $this->amount  = $amount;
        $this->isPaid  = $isPaid;
        $this->payTime = $payTime;
        $this->raw     = $raw;
    }

    /**
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return bool
     */
    public function isPaid()
    {
        return $this->isPaid;
    }

    /**
     * @return Date
     */
    public function getPayTime()
    {
        return $this->payTime;
    }

    /**
     * @return string
     */
    public function getTradeNo()
    {
        return $this->tradeNo;
    }

    /**
     * @param null $name
     * @return mixed
     */
    public function getRaw($name = null)
    {
        if (is_null($name)) {
            return $this->raw;
        } elseif (isset($this->raw[$name])) {
            return $this->raw[$name];
        }
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }
}