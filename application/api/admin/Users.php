<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-12
 * Time: 10:36
 */

namespace app\api\admin;

use app\common\builder\ZBuilder;
use think\Request;
use app\common\model\Users as UsersModel;
use app\common\model\Locals as LocalsModel;
use app\common\model\LevelSet as LevelSetModel;
use app\common\model\UsersLookNews as UsersLookNewsModel;
use app\common\model\UsersExchangeGoodsCheckIn as UsersExchangeGoodsCheckInModel;

class Users extends Base
{
    protected $model;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new UsersModel();
    }

    public function index()
    {
        $map = $this->getMap();
        $map['flag'] = 1;
        $map['info_bind'] = 1;
        $data_list = $this->model->getList($map, 'id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('users')
            ->setSearch([
                'id' => 'ID',
                'nickname' => '昵称',
                'mobile' => '手机号'
            ])
            ->addOrder('from_wechat_num,from_poster_num')
            ->addTimeFilter('create_time')
            ->addFilter('gender', [
                0 => '未知',
                1 => '男',
                2 => '女'
            ])
            ->addColumns([
                ['id', 'ID'],
                ['nickname', '昵称'],
                ['avatar', '头像', 'callback', function ($value) {
                    return "<img src='{$value}' style='width: 50px;height: 50px;'/>";
                }],
                ['mobile', '手机号'],
                ['gender', '性别', 'status', '', [0 => '未知', 1 => '男', 2 => '女']],
                ['score', '积分'],
                ['from_wechat_num', '微信分享量'],
                ['from_poster_num', '海报分享量'],
                ['status', '状态', 'switch'],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addRightButton('custom', [
                'title' => '勾销设置',
                'icon' => 'fa fa-fw fa-lightbulb-o',
                'href' => url('writerOffSet', ['id' => '__id__'])
            ])
            ->addRightButton('custom', [
                'title' => '积分设置',
                'icon' => 'fa fa-fw fa-wrench',
                'href' => url('addScore', ['id' => '__id__'])
            ])
            ->addRightButton('custom', [
                'title' => '生涯',
                'icon' => 'fa fa-fw fa-certificate',
                'href' => url('career', ['id' => '__id__'])
            ])
            ->addRightButtons(['edit' => ['title' => '查看详情'], 'delete' => ['data-tips' => '删除后无法恢复。']])
            ->setRowList($data_list)
            ->fetch();
    }

    public function systemWriterOffUsers()
    {
        $map = $this->getMap();
        $map['flag'] = 1;
        $map['info_bind'] = 1;
        $map['writer_off_role_type'] = 1;
        $data_list = $this->model->getList($map, 'id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('users')
            ->setSearch([
                'id' => 'ID',
                'nickname' => '昵称',
                'mobile' => '手机号'
            ])
            ->addTimeFilter('create_time')
            ->addFilter('gender', [
                0 => '未知',
                1 => '男',
                2 => '女'
            ])
            ->addColumns([
                ['id', 'ID'],
                ['nickname', '昵称'],
                ['avatar', '头像', 'callback', function ($value) {
                    return "<img src='{$value}' style='width: 100px;height: 100px;'/>";
                }],
                ['mobile', '手机号'],
                ['gender', '性别', 'status', '', [0 => '未知', 1 => '男', 2 => '女']],
                ['score', '积分'],
                ['status', '状态', 'switch'],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addRightButton('custom', [
                'title' => '勾销设置',
                'icon' => 'fa fa-fw fa-lightbulb-o',
                'href' => url('writerOffSet', ['id' => '__id__'])
            ])
            ->addRightButtons(['edit' => ['title' => '查看详情'], 'delete' => ['data-tips' => '删除后无法恢复。']])
            ->setRowList($data_list)
            ->fetch();
    }

    public function localsWriterOffUsers()
    {
        $map = $this->getMap();
        $map['flag'] = 1;
        $map['info_bind'] = 1;
        $map['writer_off_role_type'] = 2;
        $data_list = $this->model->getList($map, 'id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('users')
            ->setSearch([
                'id' => 'ID',
                'nickname' => '昵称',
                'mobile' => '手机号'
            ])
            ->addTimeFilter('create_time')
            ->addFilter('gender', [
                0 => '未知',
                1 => '男',
                2 => '女'
            ])
            ->addColumns([
                ['id', 'ID'],
                ['nickname', '昵称'],
                ['avatar', '头像', 'callback', function ($value) {
                    return "<img src='{$value}' style='width: 100px;height: 100px;'/>";
                }],
                ['mobile', '手机号'],
                ['gender', '性别', 'status', '', [0 => '未知', 1 => '男', 2 => '女']],
                ['score', '积分'],
                ['status', '状态', 'switch'],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addRightButton('custom', [
                'title' => '勾销设置',
                'icon' => 'fa fa-fw fa-lightbulb-o',
                'href' => url('writerOffSet', ['id' => '__id__'])
            ])
            ->addRightButtons(['edit' => ['title' => '查看详情'], 'delete' => ['data-tips' => '删除后无法恢复。']])
            ->setRowList($data_list)
            ->fetch();
    }

    public function edit($id)
    {
        if ($this->request->isPost()) {

        }
        $data = $this->model->getInfo($id);
        if (!$data) $this->error('该用户已删除或不存在');
        $gender = [
            0 => '未知',
            1 => '男',
            2 => '女'
        ];
        $data['gender'] = $gender[$data['gender']];
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['static', 'nickname', '昵称'],
                ['gallery', 'avatar', '头像'],
                ['static', 'mobile', '手机号'],
                ['static', 'gender', '性别'],
                ['static', 'score', '积分']
            ])
            ->setFormData($data)
            ->hideBtn('submit')
            ->fetch();
    }

    public function delete($ids)
    {
        $res = $this->model->del($ids);
        if ($res) $this->success('删除成功');
        $this->error('删除失败');
    }

    protected function getLocals()
    {
        $data = (new LocalsModel())->getList([
            'flag' => 1,
            'status' => 1
        ], 'id desc', 'title,id');
        $arr = [];
        foreach ($data as $item) {
            $arr[$item['id']] = $item['title'];
        }
        return $arr;
    }

    public function writerOffSet($id)
    {
        $data = $this->model->getInfo($id);
        if (!$data) $this->error('该用户已删除或不存在');
        $data['writer_off_locals_ids'] = $data['writer_off_locals_ids'] ? json_decode($data['writer_off_locals_ids'], true) : '';
        if ($this->request->isPost()) {
            $_data = input();
            $data->writer_off_role_type = $_data['writer_off_role_type'] ?: 0;
            if ($data['writer_off_role_type'] == 2)
                $data->writer_off_locals_ids = $_data['writer_off_locals_ids'] ? json_encode($_data['writer_off_locals_ids']) : '[]';
            else
                $data->writer_off_locals_ids = '';
            $data->save();
            $this->success('设置成功', 'index');
        }
        $writer_off_role_type = [
            0 => '无',
            1 => '系统勾销员',
            2 => '商家勾销员'
        ];
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['select', 'writer_off_role_type', '角色', '', $writer_off_role_type],
                ['checkbox', 'writer_off_locals_ids', '勾销商家', '当角色为【商家勾销员】时才需要选择', $this->getLocals()],
            ])
            ->setFormData($data)
            ->fetch();
    }

    public function addScore($id)
    {
        $user = $this->model->getInfo($id);
        if (!$user) $this->error('该用户已删除或不存在');
        if ($this->request->isPost()) {
            $data = input();
            $abs = $data['score'] - $user['score'];
            $user->score = $data['score'];
            $user->origin_score = $user['origin_score'] + $abs;
            $user->save();
            $this->success('修改成功', 'index');
        }
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['number', 'score', '积分', ''],
            ])
            ->setFormData($user)
            ->fetch();
    }

    public function career($id)
    {
        $user = (new UsersModel())->getInfo($id);
        if (!$user) $this->error('该用户已删除或不存在');
        $data = [];
        $data['nickname'] = $user['nickname'];
        $data['avatar'] = "<img src='{$user['avatar']}' style='width: 50px;height: 50px;'/>";
        $data['create_time'] = date('Y年m月d日', $user['create_time']);
        $data['score'] = $user['score'];
        $data['origin_score'] = $user['origin_score'];
        $level_name = (new LevelSetModel())->getLevelName($user['origin_score']);
        $data['level_name'] = $level_name ? $level_name['level_name'] : '';
        $data['comment_score'] = $user['comment_score'];
        $data['read_num'] = (new UsersLookNewsModel())->getList([
            'flag' => 1,
            'uid' => $user['id']
        ], 'id desc', null, null, null, false, true);
        $data['share_score'] = $user['share_score'];
        $data['share_wechat_score'] = $user['share_wechat_score'];
        $data['share_poster_score'] = $user['share_poster_score'];
        $data['from_wechat_num'] = $user['from_wechat_num'];
        $data['from_poster_num'] = $user['from_poster_num'];
        $data['submiss_num'] = $user['submiss_num'];
        $data['adoption_num'] = $user['adoption_num'];
        $data['adoption_score'] = $user['adoption_score'];
        $data['comment_num'] = $user['comment_num'];
        $data['exchange_num'] = (new UsersExchangeGoodsCheckInModel())->getList([
            'uid' => $user['id']
        ], 'id desc', 'id', null, null, false, true);
        return ZBuilder::make('form')
            ->addFormItems([
                ['static', 'nickname', '昵称'],
                ['static', 'avatar', '头像'],
                ['static', 'create_time', '注册时间'],
                ['static', 'score', '当前积分'],
                ['static', 'origin_score', '累计积分'],
                ['static', 'level_name', '等级称谓'],
                ['static', 'comment_score', '累计评论积分'],
                ['static', 'read_num', '阅读量'],
                ['static', 'share_score', '分享所获积分'],
                ['static', 'share_wechat_score', '分享微信好友积分'],
                ['static', 'share_poster_score', '分享海报积分'],
                ['static', 'from_wechat_num', '微信好友分享量'],
                ['static', 'from_poster_num', '海报分享量'],
                ['static', 'submiss_num', '投稿数'],
                ['static', 'adoption_num', '采纳数'],
                ['static', 'adoption_score', '采纳累计积分'],
                ['static', 'comment_num', '评论数'],
                ['static', 'exchange_num', '累计兑换奖品数量'],
            ])
            ->setFormData($data)
            ->hideBtn('submit')
            ->fetch();
    }
}