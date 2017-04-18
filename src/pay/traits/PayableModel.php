<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
namespace yunwuxin\pay\traits;

use Exception;
use Jenssegers\Date\Date;
use RuntimeException;
use yunwuxin\Pay;
use yunwuxin\pay\interfaces\Refundable;

trait PayableModel
{
    protected function getExtraAttr($extra)
    {
        return json_decode($extra, true);
    }

    protected function setExtraAttr($extra)
    {
        return json_encode($extra);
    }

    private function getAttrOrNull($name)
    {
        try {
            return $this->getAttr($name);
        } catch (Exception $e) {
            return null;
        }
    }

    public static function retrieveByTradeNo($tradeNo)
    {
        return self::get(preg_replace('/^TradeNo/', '', $tradeNo));
    }

    public function getTradeNo()
    {
        $orderNo = $this->getAttrOrNull('id');

        if ($orderNo) {
            return 'TradeNo' . $orderNo;
        }
    }

    public function getAmount()
    {
        return $this->getAttrOrNull('amount');
    }

    public function getSubject()
    {
        return $this->getAttrOrNull('subject');
    }

    public function getBody()
    {
        return $this->getAttrOrNull('body');
    }

    public function getExtra($name)
    {
        $extra = $this->getAttrOrNull('extra');

        if (isset($extra[$name])) {
            return $extra[$name];
        }
    }

    public function getExpire(callable $format)
    {
        $date = $this->getAttrOrNull('expire_time');
        if ($date) {
            return $format(Date::parse($date));
        }
    }

    /**
     * 获取渠道标识
     * @return string
     */
    public function getChannel()
    {
        $channel = $this->getAttrOrNull('channel');
        if (empty($channel)) {
            throw new RuntimeException('无法获取渠道标识!');
        }
        return $channel;
    }

    /**
     * 订单查询
     * @return mixed
     */
    public function query()
    {
        return Pay::channel($this->getChannel())->query($this);
    }

    /**
     * 支付
     * @param $gateway
     * @return mixed
     */
    public function pay($gateway)
    {
        return Pay::gateway($gateway)->purchase($this);
    }

}