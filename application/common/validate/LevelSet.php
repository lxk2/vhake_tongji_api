<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-14
 * Time: 18:08
 */

namespace app\common\validate;
class LevelSet extends Base
{
    protected $rule = [
        ['score', 'require|number', '请设置积分|请正确设置积分'],
        ['level_name' ,'require', '请设置称谓'],
        ['level_icon', 'require', '请上传icon']
    ];
}