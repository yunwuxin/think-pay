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
use yunwuxin\Pay;

trait PayableCharge
{
    private function getAttrOrNull($name)
    {
        try {
            return $this->getAttr($name);
        } catch (Exception $e) {
            return null;
        }
    }

    public static function retrieveByOrderNo($orderNo)
    {
        return self::get(preg_replace('/^NO/', '', $orderNo));
    }

    public function getOrderNo()
    {
        $orderNo = $this->getAttrOrNull('id');

        if ($orderNo) {
            return 'NO' . $orderNo;
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
        $this->getAttrOrNull('body');
    }

    public function getExtra($name)
    {
        $extra = $this->getAttrOrNull('extra');

        if (isset($extra[$name])) {
            return $extra[$name];
        }
    }

    public function pay($channel, $gateway)
    {
        return Pay::channel($channel)->useGateway($gateway)->pay($this);
    }

    public function getExpire(callable $format)
    {
        $date = $this->getAttrOrNull('expire_time');
        if ($date) {
            return $format(Date::parse($date));
        }
    }
}