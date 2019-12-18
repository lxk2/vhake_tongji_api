<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-08
 * Time: 20:35
 */

namespace app\common\validate;
class News extends Base
{
    protected $rule = [
        ['cats_id', 'require|number', '请选择所属分类|请正确选择分类'],
        ['title', 'require', '请输入资讯标题'],
        ['content', 'require', '请输入资讯内容'],
        ['pic', 'require', '请上传封面图'],
        ['source', 'require', '请输入来源/作者'],
    ];
}