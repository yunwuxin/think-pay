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
use yunwuxin\pay\interfaces\Payable;

trait RefundableModel
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

    public function getRefundNo()
    {
        $refundNo = $this->getAttrOrNull('id');

        if ($refundNo) {
            return 'RefundNo' . $refundNo;
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

    public function getChannel()
    {
        return $this->getAttrOrNull('channel');
    }

    /**
     * @return Payable
     */
    public function getCharge()
    {
        return $this->getAttr('charge');
    }

    public function refund()
    {
        return Pay::channel($this->getChannel())->refund($this);
    }

    public function query()
    {
        return Pay::channel($this->getChannel())->refundQuery($this);
    }
}