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

namespace yunwuxin\pay\traits;

use Exception;
use yunwuxin\Pay;

trait TransferableModel
{
    protected function getExtraAttr($extra)
    {
        return json_decode($extra, true);
    }

    protected function setExtraAttr($extra)
    {
        return json_encode($extra);
    }

    private function getAttrOrNull($name)
    {
        try {
            return $this->getAttr($name);
        } catch (Exception $e) {
            return null;
        }
    }

    public function getTransferNo()
    {
        $refundNo = $this->getAttrOrNull('id');

        if ($refundNo) {
            return 'TransferNo' . $refundNo;
        }
    }

    public function getExtra($name)
    {
        $extra = $this->getAttrOrNull('extra');

        if (isset($extra[$name])) {
            return $extra[$name];
        }
    }

    public function getAmount()
    {
        return $this->getAttrOrNull('amount');
    }

    public function getRealName()
    {
        return $this->getAttrOrNull('realname');
    }

    public function getAccount()
    {
        return $this->getAttrOrNull('account');
    }

    public function getChannel()
    {
        return $this->getAttrOrNull('channel');
    }

    public function getRemark()
    {
        return $this->getAttrOrNull('remark');
    }

    public function transfer()
    {
        return Pay::channel($this->getChannel())->transfer($this);
    }
}