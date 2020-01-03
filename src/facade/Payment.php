<?php

namespace yunwuxin\pay\facade;

use think\Facade;

class Payment extends Facade
{
    protected static function getFacadeClass()
    {
        return \yunwuxin\pay\Payment::class;
    }
}
