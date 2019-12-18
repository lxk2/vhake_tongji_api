<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-26
 * Time: 10:41
 */

namespace app\api\admin;
use app\common\builder\ZBuilder;
use think\Request;
use app\common\model\UsersShareLog as UsersShareLogModel;
use app\common\model\Users as UsersModel;

class UsersShareLog extends Base
{
    protected $model, $user_model;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new UsersShareLogModel();
        $this->user_model = new UsersModel();
    }

    public function index()
    {
        $map = $this->getMap();
        $map['flag'] = 1;
        $data_list = $this->model->getList($map, 'id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('users_share_log')
            ->addColumns([
                ['id', 'ID'],
                ['uid', '用户信息', 'callback', function ($value) {
                    $url = url('users/edit', ['id' => $value]);
                    $user = $this->user_model->getInfo($value, [

                    ], 'id,nickname,avatar');
                    $nickname = $user['nickname'];
                    $avatar = $user['avatar'];
                    return "<div style='display: flex;align-items: center;'>
    ID：<a href='{$url}' style='margin-right: 10px;'>{$value}</a>
    昵称：<font style='margin-right: 10px;'>{$nickname}</font>
    头像：<img src='{$avatar}' style='width: 100px;height: 100px;'/>
</div>";
                }],
                ['share_type', '分享类型', 'status', '', [
                    '0' => '',
                    '1' => '海报',
                    '2' => '微信好友'
                ]],
                ['news_id', '资讯id', 'callback', function ($value) {
                    $url = url('news/detail', ['id' => $value]);
                    return "<a href='{$url}'>{$value}</a>";
                }],
                ['local_id', '商家id', 'callback', function ($value) {
                    $url = url('locals/detail', ['id' => $value]);
                    return "<a href='{$url}'>{$value}</a>";
                }],
                ['score', '所得积分'],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addRightButtons(['delete' => ['data-tips' => '删除后无法恢复。']])
            ->setRowList($data_list)
            ->fetch();
    }

    public function delete($ids)
    {
        $res = $this->model->del($ids);
        if ($res) $this->success('删除成功');
        $this->error('删除失败');
    }
}