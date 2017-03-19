<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace yunwuxin\pay\model;

use Jenssegers\Date\Date;
use think\Model;
use yunwuxin\pay\interfaces\Payable;
use yunwuxin\pay\traits\PayableCharge;

/**
 * Class Charge
 * @package yunwuxin\pay
 *
 * @property integer $id
 * @property string  $subject
 * @property string  $body
 * @property integer $amount
 * @property Date    $expire_time
 * @property Date    $create_time
 * @property Date    $update_time
 * @property array   $extra
 */
class Charge extends Model implements Payable
{
    use PayableCharge;

    protected $type = [
        'extra' => 'json'
    ];

}