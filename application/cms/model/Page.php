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

namespace app\cms\model;

use think\Model as ThinkModel;

/**
 * 单页模型
 * @package app\cms\model
 */
class Page extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__CMS_PAGE__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 获取单页标题列表
     * @author Vhake <admin@vhake.com>
     * @return array|mixed
     */
    public static function getTitleList()
    {
        $result = cache('cms_page_title_list');
        if (!$result) {
            $result = self::where('status', 1)->column('id,title');
            // 非开发模式，缓存数据
            if (config('develop_mode') == 0) {
                cache('cms_page_title_list', $result);
            }
        }
        return $result;
    }
}