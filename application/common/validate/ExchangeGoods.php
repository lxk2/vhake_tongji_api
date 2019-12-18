<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-13
 * Time: 22:56
 */

namespace app\common\validate;
class ExchangeGoods extends Base
{
    protected $rule = [
        ['locals_id', 'require|number', '请选择商家|请正确选择商家'],
        ['score', 'require|number', '请设置所需积分|请正确设置所需积分'],
        ['title', 'require', '请设置标题'],
        ['pic', 'require', '请上传封面图'],
        ['num', 'require|number', '请设置数量|请正确设置数量'],
        ['sort', 'number', '请正确设置排序'],
        ['end_time', 'require', '请设置有效期']
    ];
}