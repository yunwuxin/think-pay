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

return [
    'sandbox'    => true,//沙箱模式
    'charge'     => 'app\\model\\Charge',
    'channels'   => [
        'alipay' => [
            'type'                  => 'alipay',
            'app_id'                => '',
            'app_private_key'       => '',//应用私钥
            //以下为公钥模式参数
            'alipay_public_key'     => '', //支付宝公钥
            //以下为公钥证书模式参数
            'app_cert_path'         => '',//硬要公钥证书路径
            'alipay_cert_path'      => '',//支付宝公钥证书路径
            'alipay_root_cert_path' => '',//支付宝根证书路径
        ],
        'wechat' => [
            'type'    => 'wechat',
            'key'     => '',
            'app_id'  => '',
            'mch_id'  => '',
            'cert'    => '',//证书
            'ssl_key' => '',//证书秘钥
        ],
    ],
    'notify_url' => '',//留空则设为PAY_NOTIFY对应的路由
    'route'      => true,//是否注册路由
];
