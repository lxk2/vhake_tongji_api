<?php
// +----------------------------------------------------------------------
// | VhakePHP [QiqiStudio]
// +----------------------------------------------------------------------
// | 版权所有 2015~2018 Vhake Shenzhen
// +----------------------------------------------------------------------
// | 官方网站: http://www.vhake.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

namespace plugins\HelloWorld\model;

use app\common\model\Plugin;

/**
 * 后台插件模型
 * @package plugins\HelloWorld\model
 */
class HelloWorld extends Plugin
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__PLUGIN_HELLO__';

    public function test()
    {
        // 获取插件的设置信息
        halt('test');
    }
}