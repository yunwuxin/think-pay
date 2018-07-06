<?php
/**
 * Created by PhpStorm.
 * User: yunwuxin
 * Date: 2018/7/6
 * Time: 15:30
 */

namespace yunwuxin\pay\gateway\wechat;


use yunwuxin\pay\entity\ParamResult;
use yunwuxin\pay\Gateway;
use yunwuxin\pay\interfaces\Payable;

class Xiao extends Gateway
{

    /**
     * 付款
     * @param Payable $charge
     * @return ParamResult
     */
    public function purchase(Payable $charge)
    {
        $param = $this->channel->buildXiaoParams($charge);
        return new ParamResult($param);
    }
}