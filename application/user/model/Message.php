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

namespace app\user\model;

use think\Model;

/**
 * 角色模型
 * @package app\admin\model
 */
class Message extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'admin_message';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 获取当前用户未读消息数量
     * @author Vhake <admin@vhake.com>
     * @return int|string
     */
    public static function getMessageCount()
    {
        return self::where(['status' => 0, 'uid_receive' => UID])->count();
    }
}