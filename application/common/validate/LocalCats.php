<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-12
 * Time: 16:34
 */

namespace app\common\validate;
class LocalCats extends Base
{
    protected $rule = [
        ['title', 'require|max:10', '请输入分类名称|分类名称过长'],
        ['sort', 'number', '请正确输入数字作为排序']
    ];
}