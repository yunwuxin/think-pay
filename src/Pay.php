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
use think\facade\Config;
use think\helper\Str;
use yunwuxin\pay\Channel;
use yunwuxin\pay\Gateway;

class Pay
{
    /** @var Channel[] */
    protected static $channels = [];

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
     * 创建渠道
     * @param string $channelName
     * @return Channel
     */
    protected static function buildChannel($channelName)
    {
        list($name, $group) = explode('@', $channelName . '@');

        $className = "\\yunwuxin\\pay\\channel\\" . Str::studly($name);
        $channels  = Config::get('pay.channels');
        if (class_exists($className) && isset($channels[$name])) {

            if (!empty($group) && isset($channels[$name][$group])) {
                $config = $channels[$name][$group];
            } else {
                $config = $channels[$name];
            }

            /** @var Channel $channel */
            $channel = new $className($config);

            $notifyUrl = Config::get('pay.notify_url');
            if ($notifyUrl) {
                $channel->setNotifyUrl(str_replace(':channel', $channelName, $notifyUrl));
            } else {
                $channel->setNotifyUrl(url('PAY_NOTIFY', ['channel' => $channelName], '', true));
            }

            if (Config::get('pay.test')) {
                $channel->setTest();
            }
            return $channel;
        }
        throw new InvalidArgumentException("Channel [{$name}] not supported.");
    }
}