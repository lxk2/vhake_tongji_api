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
 * 滚动图片验证器
 * @package app\cms\validate
 * @author Vhake <admin@vhake.com>
 */
class Slider extends Validate
{
    // 定义验证规则
    protected $rule = [
        'title|标题' => 'require|length:1,30',
        'cover|图片' => 'require',
//        'url|链接'   => 'require|url',
    ];

    // 定义验证场景
    protected $scene = [
        'title' => ['title'],
//        'url'   => ['url'],
    ];
}
