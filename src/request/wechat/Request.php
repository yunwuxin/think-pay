<?php

namespace yunwuxin\pay\request\wechat;

use function yunwuxin\pay\array2xml;

abstract class Request extends \yunwuxin\pay\Request
{
    protected $endpoint = 'https://api.mch.weixin.qq.com';

    protected $uri;

    public function getMethod()
    {
        return 'POST';
    }

    public function getUri()
    {
        if ($this->channel->isSandbox()) {
            return $this->endpoint . '/sandboxnew/' . $this->uri;
        }
        return $this->endpoint . '/' . $this->uri;
    }

    public function getHeaders()
    {
        return [];
    }

    public function getBody()
    {
        $params         = $this->params;
        $params['sign'] = $this->channel->generateSign($params);

        return array2xml($params);
    }
}
