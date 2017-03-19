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

namespace yunwuxin\pay\channel;

use yunwuxin\pay\Channel;
use yunwuxin\pay\exception\ConfigException;
use yunwuxin\pay\gateway\wechat\App;
use yunwuxin\pay\gateway\wechat\Pub;
use yunwuxin\pay\gateway\wechat\PubQrCode;

class Wechat extends Channel
{
    const APP    = 'app';
    const PUB    = 'pub';
    const PUB_QR = 'pub_qr_code';

    protected static $gateways = [
        self::APP    => App::class,
        self::PUB    => Pub::class,
        self::PUB_QR => PubQrCode::class
    ];

    protected $appId;
    protected $mchId;
    protected $key;

    public function __construct($config)
    {
        if (empty($config['app_id']) || empty($config['mch_id']) || empty($config['key'])) {
            throw new ConfigException;
        }
        $this->appId = $config['app_id'];
        $this->mchId = $config['mch_id'];
        $this->key   = $config['key'];
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @return mixed
     */
    public function getMchId()
    {
        return $this->mchId;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }
}