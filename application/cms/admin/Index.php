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

namespace app\cms\admin;

use app\admin\controller\Admin;
use think\Db;

/**
 * 仪表盘控制器
 * @package app\cms\admin
 */
class Index extends Admin
{
    /**
     * 首页
     * @author Vhake <admin@vhake.com>
     * @return mixed
     */
    public function index()
    {
        $this->assign('document', Db::name('cms_document')->where('trash', 0)->count());
        $this->assign('column', Db::name('cms_column')->count());
        $this->assign('page', Db::name('cms_page')->count());
        $this->assign('model', Db::name('cms_model')->count());
        $this->assign('page_title', '仪表盘');
        return $this->fetch(); // 渲染模板
    }
}