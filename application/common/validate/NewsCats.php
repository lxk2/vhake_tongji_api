<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-07
 * Time: 23:03
 */

namespace app\common\validate;
class NewsCats extends Base
{
    protected $rule = [
        ['title', 'require|max:10', '请输入分类名称|分类名称过长'],
        ['sort', 'number', '请正确输入数字作为排序']
    ];
}