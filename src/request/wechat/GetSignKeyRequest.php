<?php

namespace yunwuxin\pay\request\wechat;

use think\helper\Str;

class GetSignKeyRequest extends Request
{
    public function __construct($mchId)
    {
        $this->params = [
            'mch_id'    => $mchId,
            'nonce_str' => Str::random(),
        ];
    }

    public function getUri()
    {
        return $this->endpoint . "/sandboxnew/pay/getsignkey";
    }
}
