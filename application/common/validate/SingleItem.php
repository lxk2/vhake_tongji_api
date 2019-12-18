<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-13
 * Time: 15:05
 */

namespace app\common\validate;
class SingleItem extends Base
{
    protected $rule = [
        ['item_key', 'require' ,'请输入item_key'],
        ['item_value', 'require', '请输入item_value'],
    ];
}