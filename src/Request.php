<?php

namespace yunwuxin\pay;

abstract class Request
{
    /** @var Channel */
    protected $channel;

    protected $params = [];

    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    abstract public function getMethod();

    abstract public function getUri();

    abstract public function getHeaders();

    abstract public function getBody();

    public function toPsrRequest()
    {
        return new \GuzzleHttp\Psr7\Request(
            $this->getMethod(),
            $this->getUri(),
            $this->getHeaders(),
            $this->getBody()
        );
    }
}
