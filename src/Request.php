<?php

namespace yunwuxin\pay;

abstract class Request
{
    /** @var Channel */
    protected $channel;

    protected $params = [];

    public function setChannel(Channel $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    abstract public function getMethod();

    abstract public function getUri();

    abstract public function getHeaders();

    abstract public function getBody();
}
