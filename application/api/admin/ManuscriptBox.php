<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-14
 * Time: 16:36
 */

namespace app\api\admin;

use app\api\home\v1\Wx;
use app\common\builder\ZBuilder;
use think\Db;
use think\Request;
use app\common\model\ManuscriptBox as ManuscriptBoxModel;
use app\common\model\AdoptionRewardLog as AdoptionRewardLogModel;
use app\common\model\Users as UsersModel;
use app\common\model\Formid as FormidModel;

class ManuscriptBox extends Base
{
    protected $model;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ManuscriptBoxModel();
    }

    public function index()
    {
        $map = $this->getMap();
        $map['flag'] = 1;
        $data_list = $this->model->getList($map, 'id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('manuscript_box')
            ->addColumns([
                ['id', 'ID'],
                ['uid', '用户id', 'callback', function ($value) {
                    $url = url('users/edit', ['id' => $value]);
                    return "<a href='{$url}'>{$value}</a>";
                }],
                ['title', '标题'],
                ['status', '状态', 'status', '', [
                    -1 => '不采纳',
                    0 => '待审核',
                    1 => '已采纳'
                ]],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addRightButton('custom', [
                'title' => '采纳通过',
                'icon' => 'fa fa-fw fa-check',
                'href' => url('adoption', ['id' => '__id__'])
            ])
            ->addRightButton('custom', [
                'title' => '搁置',
                'icon' => 'fa fa-fw fa-thumbs-o-down',
                'href' => url('shelving', ['id' => '__id__'])
            ])
            ->addRightButtons(['edit' => ['title' => '查看详情'], 'delete' => ['data-tips' => '删除后无法恢复。']])
            ->setRowList($data_list)
            ->fetch();
    }

    /**
     * 采纳列表
     */
    public function adoptionList()
    {
        $map = $this->getMap();
        $map['flag'] = 1;
        $map['status'] = 1;
        $data_list = $this->model->getList($map, 'id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('manuscript_box')
            ->addColumns([
                ['id', 'ID'],
                ['uid', '用户id', 'callback', function ($value) {
                    $url = url('users/edit', ['id' => $value]);
                    return "<a href='{$url}'>{$value}</a>";
                }],
                ['title', '标题'],
                ['score', '奖励积分'],
                ['status', '状态', 'status', '', [
                    -1 => '不采纳',
                    0 => '待审核',
                    1 => '已采纳'
                ]],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addRightButton('custom', [
                'title' => '采纳通过',
                'icon' => 'fa fa-fw fa-check',
                'href' => url('adoption', ['id' => '__id__'])
            ])
            ->addRightButton('custom', [
                'title' => '搁置',
                'icon' => 'fa fa-fw fa-thumbs-o-down',
                'href' => url('shelving', ['id' => '__id__'])
            ])
            ->addRightButtons(['edit' => ['title' => '查看详情'], 'delete' => ['data-tips' => '删除后无法恢复。']])
            ->setRowList($data_list)
            ->fetch();
    }

    /**
     * 搁置列表
     */
    public function shelvingList()
    {
        $map = $this->getMap();
        $map['flag'] = 1;
        $map['status'] = -1;
        $data_list = $this->model->getList($map, 'id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('manuscript_box')
            ->addColumns([
                ['id', 'ID'],
                ['uid', '用户id', 'callback', function ($value) {
                    $url = url('users/edit', ['id' => $value]);
                    return "<a href='{$url}'>{$value}</a>";
                }],
                ['title', '标题'],
                ['status', '状态', 'status', '', [
                    -1 => '不采纳',
                    0 => '待审核',
                    1 => '已采纳'
                ]],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addRightButton('custom', [
                'title' => '采纳通过',
                'icon' => 'fa fa-fw fa-check',
                'href' => url('adoption', ['id' => '__id__'])
            ])
            ->addRightButton('custom', [
                'title' => '搁置',
                'icon' => 'fa fa-fw fa-thumbs-o-down',
                'href' => url('shelving', ['id' => '__id__'])
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
        if (!$data) $this->error('该记录已删除或不存在');
        $uid_url = url('users/edit', ['id' => $data['uid']]);
        $data['uid'] = "<a href='{$uid_url}'>{$data['uid']}</a>";
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['static', 'uid', '用户id'],
                ['static', 'title', '标题'],
                ['gallery', 'pic', '图片'],
                ['archive', 'video_url', '视频'],
                ['textarea', 'content', '内容'],
            ])
            ->hideBtn('submit')
            ->setFormData($data)
            ->fetch();
    }

    public function delete($ids)
    {
        $res = $this->model->del($ids);
        if ($res) $this->success('删除成功');
        $this->error('删除失败');
    }

    public function adoption($id)
    {
        if ($this->request->isPost()) {
            Db::startTrans();
            $data = input();
            if (!$data['score']) $this->error('请设置奖励积分');
            $box = $this->model->getInfo($id);
            if (!$box) $this->error('该记录已删除或不存在');
            if ($box['status'] != 0) $this->error('该记录已处理');
            $box->status = 1;
            $box->score = $data['score'];
            $box->save();
            $model = new AdoptionRewardLogModel();
            $model->allowField(true)->isUpdate(false)->data([
                'uid' => $box['uid'],
                'score' => $data['score'],
                'manuscript_box_id' => $box['id']
            ])->save();
            if (!$model->id) {
                Db::rollback();
                $this->error('采纳失败');
            }
            // 用户积分增加
            $user = (new UsersModel())->getInfo($box['uid']);
            if ($user) {
                $user->score = $user['score'] + $data['score'];
                $user->origin_score = $user['origin_score'] + $data['score'];
                $user->adoption_num = $user['adoption_num'] + 1;
                $user->adoption_score = $user['adoption_score'] + $data['score'];
                $user->save();
                // 模板消息通知
                $formid = (new FormidModel())->getFormIdByUserId($user['id']);
                if ($formid) {
                    $res = (new Wx())->sendTemplateMessage($user['small_openid'], config('template_id')['notice_of_adoption_of_works'], $formid['formid'], [
                        'keyword1' => [
                            'value' => $box['title']
                        ],
                        'keyword2' => [
                            'value' => '已采纳'
                        ],
                        'keyword3' => [
                            'value' => '奖励' . $data['score'] . '积分'
                        ]
                    ], 'pages/index/index');
                    if ($res['errcode'] != 0) {
                        Db::rollback();
                        $this->error($res['errmsg']);
                    }
                    $formid->flag = -1;
                    $formid->save();
                }
            }
            Db::commit();
            $this->success('采纳成功', 'index');
        }
        return ZBuilder::make('form')
            ->addFormItems([
                ['number', 'score', '奖励积分']
            ])
            ->fetch();
    }

    public function shelving($id)
    {
        $box = $this->model->getInfo($id);
        if (!$box) $this->error('该记录已删除或不存在');
        if ($box['status'] != 0) $this->error('该记录已处理');
        $box->status = -1;
        $box->save();
        $this->success('搁置成功', 'index');
    }
}