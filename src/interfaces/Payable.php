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
namespace yunwuxin\pay\interfaces;

use yunwuxin\pay\entity\PurchaseResult;

interface Payable
{
    public function getTradeNo();

    public function getAmount();

    public function getSubject();

    public function getBody();

    public function getExtra($name);

    public function getExpire(callable $format);

    public function getReturnUrl();

    /**
     * @return bool
     */
    public function isComplete();

    public function onComplete(PurchaseResult $result);

    /**
     * @param $orderNo
     * @return self
     */
    public static function retrieveByTradeNo($orderNo);

}
