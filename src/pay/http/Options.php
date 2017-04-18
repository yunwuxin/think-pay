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

use function array_merge;

class Options
{
    protected $headers = [];

    protected $query = [];

    protected $params = [];

    protected $useJson = false;

    protected $body;

    protected $extra = [];

    public static function makeWithQuery(array $query)
    {
        return (new self())->setQuery($query);
    }

    public static function makeWithParams(array $params)
    {
        return (new self())->setParams($params);
    }

    public static function makeWithBody($body)
    {
        return (new self())->setBody($body);
    }

    public function getHeader($name = null)
    {
        return is_null($name) ? $this->headers : $this->headers[$name];
    }

    public function setHeader($name, $value = null)
    {
        if (is_array($name)) {
            $this->headers = array_merge($this->headers, $name);
        } else {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    public function setQuery($name, $value = null)
    {
        if (is_array($name)) {
            $this->query = array_merge($this->query, $name);
        } else {
            $this->query[$name] = $value;
        }
        return $this;
    }

    public function getQuery($name = null)
    {
        return is_null($name) ? $this->query : $this->query[$name];
    }

    public function setParams($name, $value = null)
    {
        if (is_array($name)) {
            $this->params = array_merge($this->params, $name);
        } else {
            $this->params[$name] = $value;
        }
        return $this;
    }

    public function getParams($name = null)
    {
        return is_null($name) ? $this->params : $this->params[$name];
    }

    public function setExtra($name, $value = null)
    {
        if (is_array($name)) {
            $this->extra = array_merge($this->extra, $name);
        } else {
            $this->extra[$name] = $value;
        }
        return $this;
    }

    /**
     * @param mixed $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function useJson()
    {
        $this->useJson = true;
        return $this;
    }

    public function toArray()
    {
        $arr = [
            'headers' => $this->headers,
            'query'   => $this->query
        ];
        if (!empty($this->body)) {
            $arr['body'] = $this->body;
        } else {
            if ($this->useJson) {
                $arr['json'] = $this->params;
            } else {
                $arr['form_params'] = $this->params;
            }
        }
        return array_merge($arr, $this->extra);
    }
}