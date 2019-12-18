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

namespace plugins\DySms\model;

use app\common\model\Plugin;

/**
 * 后台插件模型
 * @package plugins\DySms\model
 * @author 小乌 <82950492@qq.com>
 */
class DySms extends Plugin
{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'plugin_dysms';

    /**
     * 获取模板数据
     * @param string $title 模板名称
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function getTemplate($title = '')
    {
        return self::where('title', $title)->find();
    }
}