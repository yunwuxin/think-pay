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

interface Payable
{
    public function getOrderNo();

    public function getAmount();

    public function getSubject();

    public function getBody();

    public function getExtra($name);

    public function getExpire(callable $format);

    public static function retrieveByOrderNo($orderNo);
}