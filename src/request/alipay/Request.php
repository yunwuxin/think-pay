<?php

namespace yunwuxin\pay\request\alipay;

use Carbon\Carbon;

abstract class Request extends \yunwuxin\pay\Request
{
    protected $endpoint        = 'https://openapi.alipay.com/gateway.do';
    protected $sandboxEndpoint = "https://openapi.alipaydev.com/gateway.do";

    protected $method;

    protected $bizContent = [];

    protected function getCommonParams()
    {
        return [
            'app_id'    => $this->channel->getOption('app_id'),
            'method'    => $this->method,
            'format'    => 'JSON',
            'charset'   => 'utf-8',
            'sign_type' => $this->channel->getOption('sign_type'),
            'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
            'version'   => '1.0',
        ];
    }

    public function getMethod()
    {
        return 'POST';
    }

    public function getHeaders()
    {
        return [];
    }

    public function getBody()
    {
        return null;
    }

    public function getUri(): string
    {
        if ($this->channel->isSandbox()) {
            $endpoint = $this->sandboxEndpoint;
        } else {
            $endpoint = $this->endpoint;
        }

        $params = array_merge($this->getCommonParams(), $this->params);

        $params['biz_content'] = json_encode(array_filter($this->bizContent), JSON_UNESCAPED_UNICODE);
        $params['sign']        = $this->channel->generateSign($params);

        return $endpoint . '?' . http_build_query($params);
    }
}
