<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-12
 * Time: 17:40
 */

namespace app\common\validate;
class Locals extends Base
{
    protected $rule = [
        ['title', 'require', '请输入标题'],
        ['cats_id', 'require|number', '请选择所属分类|请正确选择所属分类'],
        ['pic', 'require', '请上传封面图'],
        ['loop_pic', 'require', '请上传轮播图'],
        ['mobile', 'require', '请输入联系电话'],
        ['map', 'require', '请输入商家地址'],
        ['map_address', 'require', '请输入商家地址'],
        ['content', 'require', '请输入内容'],
        ['sort', 'number', '请正确输入排序'],
    ];
}