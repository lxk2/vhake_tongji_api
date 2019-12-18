<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-12
 * Time: 14:26
 */

namespace app\common\validate;
class ShareAppMessage extends Base
{
    protected $rule = [
        ['title', 'require', '请输入分享标题'],
        ['pic', 'require', '请上传分享配图'],
    ];
}