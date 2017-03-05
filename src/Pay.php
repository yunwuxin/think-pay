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
namespace yunwuxin;

use InvalidArgumentException;
use think\Config;
use yunwuxin\pay\Channel;

class Pay
{
    /** @var Channel[] */
    protected static $channels = [];

    /**
     * 获取一个社会化渠道
     * @param string $name
     * @return Channel
     */
    public static function channel($name)
    {
        $name = strtolower($name);
        if (!isset(self::$channels[$name])) {
            self::$channels[$name] = self::buildChannel($name);
        }
        return self::$channels[$name];
    }

    /**
     * 创建渠道
     * @param string $name
     * @return Channel
     */
    protected static function buildChannel($name)
    {
        $className = "\\yunwuxin\\pay\\channel\\" . ucfirst($name);
        $channels  = Config::get('social.channels');
        if (class_exists($className) && isset($channels[$name])) {
            return new $className($channels[$name]);
        }
        throw new InvalidArgumentException("Channel [{$name}] not supported.");
    }
}