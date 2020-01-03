<?php

namespace yunwuxin\pay\entity;

use JsonSerializable;

class PurchaseResponse implements JsonSerializable
{
    const TYPE_REDIRECT = 'redirect';
    const TYPE_SCAN     = 'scan';
    const TYPE_PARAMS   = 'params';

    protected $data;

    protected $type;

    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
        ];
    }
}
