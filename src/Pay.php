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
use think\helper\Str;
use yunwuxin\pay\Channel;
use yunwuxin\pay\Gateway;

class Pay
{
    /** @var Channel[] */
    protected static $channels = [];

    /**
     * 获取一个支付渠道
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
     * 获取一个付款网关
     * @param $name
     * @return Gateway
     */
    public static function gateway($name)
    {
        list($channel, $gateway) = explode('.', $name);
        return self::channel($channel)->gateway($gateway);
    }

    /**
     * 创建渠道
     * @param string $name
     * @return Channel
     */
    protected static function buildChannel($name)
    {
        $className = "\\yunwuxin\\pay\\channel\\" . Str::studly($name);
        $channels  = Config::get('pay.channels');
        if (class_exists($className) && isset($channels[$name])) {
            /** @var Channel $channel */
            $channel = new $className($channels[$name]);
            $channel->setNotifyUrl(url('PAY_NOTIFY', ['channel' => $name], '', true));
            if (Config::get('pay.test')) {
                $channel->setTest();
            }
            return $channel;
        }
        throw new InvalidArgumentException("Channel [{$name}] not supported.");
    }
}