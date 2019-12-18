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

namespace app\cms\validate;

use think\Validate;

/**
 * 友情链接验证器
 * @package app\cms\validate
 * @author Vhake <admin@vhake.com>
 */
class Link extends Validate
{
    // 定义验证规则
    protected $rule = [
        'title|链接标题' => 'require|length:1,30',
        'url|链接地址'   => 'require|url',
        'logo|链接LOGO' => 'requireIf:type,2',
    ];

    // 定义验证提示
    protected $message = [
        'logo.requireIf' => '请上传链接LOGO',
    ];

    // 定义验证场景
    protected $scene = [
        'title' => ['title'],
        'url'   => ['url' => 'require'],
    ];
}
