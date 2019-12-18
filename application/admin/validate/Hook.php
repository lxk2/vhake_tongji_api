<?php
// +----------------------------------------------------------------------
// | Vhake框架 [ VhakeAdmin ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 深圳市威骇客网络科技有限公司 [ http://www.vhake.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://VhakeAdmin.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

namespace app\admin\validate;

use think\Validate;

/**
 * 钩子验证器
 * @package app\admin\validate
 * @author Vhake <admin@vhake.com>
 */
class Hook extends Validate
{
    //定义验证规则
    protected $rule = [
        'name|钩子名称'  => 'require|regex:^[a-zA-Z]\w{0,39}$|unique:admin_hook'
    ];

    //定义验证提示
    protected $message = [
        'name.regex' => '钩子名称由字母和下划线组成',
    ];
}
