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

namespace yunwuxin\pay\http;

class Client
{

    public static function get($uri, Options $options = null)
    {
        return self::request('get', $uri, $options);
    }

    public static function post($uri, Options $options = null)
    {
        return self::request('post', $uri, $options);
    }

    public static function put($uri, Options $options = null)
    {
        return self::request('put', $uri, $options);
    }

    public static function delete($uri, Options $options = null)
    {
        return self::request('delete', $uri, $options);
    }

    protected static function request($method, $uri, Options $options = null)
    {
        $client  = new \GuzzleHttp\Client();
        $options = $options ? $options->toArray() : [];
        return $client->request($method, $uri, $options);
    }
}