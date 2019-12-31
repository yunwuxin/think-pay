<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace yunwuxin\pay\entity;

class RefundResult
{

    protected $raw;

    protected $channel;

    public function __construct($channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param null $name
     * @return mixed
     */
    public function getRaw($name = null)
    {
        if (is_null($name)) {
            return $this->raw;
        } elseif (isset($this->raw[$name])) {
            return $this->raw[$name];
        }
    }
}