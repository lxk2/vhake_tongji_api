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

namespace app\common\hook\model;
use think\Model;
/**
 * @package app\ucenter\model
 */
class SysHook extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__SYS_HOOK__';

    protected $autoWriteTimestamp = true;


}