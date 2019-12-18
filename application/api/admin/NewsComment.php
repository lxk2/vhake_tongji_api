<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-12
 * Time: 10:28
 */

namespace app\api\admin;

use app\common\builder\ZBuilder;
use think\Db;
use think\Request;
use app\common\model\NewsComment as NewsCommentModel;
use app\common\model\News as NewsModel;

class NewsComment extends Base
{
    protected $model;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new NewsCommentModel();
    }

    protected function getNewsTitle()
    {
        $data = (new NewsModel())->getList([
            'flag' => 1
        ], 'id desc', 'id,title');
        $arr = [];
        foreach ($data as $item) {
            $arr[$item['id']] = $item['title'];
        }
        return $arr;
    }

    public function index()
    {
        $map = $this->getMap();
        $map['flag'] = 1;
        $map['pid'] = 0;
        $data_list = $this->model->getList($map, 'id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('news_comment')
            ->setSearch([
                'id' => 'ID',
                'news_id' => '资讯id'
            ])
            ->addTimeFilter('create_time')
            ->addFilter('news_id', $this->getNewsTitle())
            ->addColumns([
                ['id', 'ID'],
                ['news_id', '资讯id', 'callback', function ($value) {
                    if ($value)
                        $url = url('news/detail', ['id' => $value]);
                    else
                        $url = 'javascript:;';
                    return "<a href='{$url}'>{$value}</a>";
                }],
                ['uid', '用户信息', 'callback', function ($value, $data) {
                    if ($value)
                        $url = url('users/edit', ['id' => $value]);
                    else
                        $url = 'javascript:;';
                    return "<a href='{$url}'>id: {$value}</a>" . "<font style='margin-left: 10px;'>{$data['u_nickname']}</font>";
                }, '__data__'],
                ['content', '评论内容'],
                ['comment_num', '评论数'],
                ['like_num', '点赞数'],
                ['status', '状态', 'switch'],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addRightButton('custom', [
                'title' => '查看回复',
                'icon' => 'fa fa-fw fa-mail-reply',
                'href' => url('replay', ['id' => '__id__'])
            ])
            ->addRightButtons(['edit' => ['title' => '查看详情'], 'delete' => ['data-tips' => '删除后无法恢复。']])
            ->setRowList($data_list)
            ->fetch();
    }

    public function replay($id)
    {
        $prev_comment = $this->model->getInfo($id, [
            'flag' => 1
        ], 'id,u_nickname');
        if (!$prev_comment) $this->error('该评论已删除或不存在');
        $map = $this->getMap();
        $map['flag'] = 1;
        $data_list = $this->model->getList($map, 'id asc', null, null, null, false, false);
        $data_list = json_decode(json_encode($data_list, true), true);
        $data = getLevelTree($data_list, $id, 0);
        $list = [];
        foreach ($data as $item) {
            if ($item['level'] == 0) {
                $item['prev_comment_nickname'] = $prev_comment['u_nickname'];
                array_push($list, $item);
            }
        }
        return ZBuilder::make('table')
            ->setTableName('news_comment')
            ->setSearch([
                'id' => 'ID',
                'news_id' => '资讯id'
            ])
            ->addTimeFilter('create_time')
            ->addFilter('news_id', $this->getNewsTitle())
            ->addColumns([
                ['id', 'ID'],
                ['news_id', '资讯id', 'callback', function ($value) {
                    if ($value)
                        $url = url('news/detail', ['id' => $value]);
                    else
                        $url = 'javascript:;';
                    return "<a href='{$url}'>{$value}</a>";
                }],
                ['uid', '用户信息', 'callback', function ($value, $data) {
                    if ($value)
                        $url = url('users/edit', ['id' => $value]);
                    else
                        $url = 'javascript:;';
                    return "<a href='{$url}'>id: {$value}</a>" . "<font style='margin-left: 10px;'>{$data['u_nickname']}</font>";
                }, '__data__'],
                ['prev_comment_nickname', '回复', 'callback', function ($value) {
                    return '回复：@'. $value;
                }],
                ['content', '评论内容'],
                ['comment_num', '评论数'],
                ['like_num', '点赞数'],
                ['status', '状态', 'switch'],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addRightButton('custom', [
                'title' => '查看回复',
                'icon' => 'fa fa-fw fa-mail-reply',
                'href' => url('replay', ['id' => '__id__'])
            ])
            ->addRightButtons(['edit' => ['title' => '查看详情'], 'delete' => ['data-tips' => '删除后无法恢复。']])
            ->setRowList($list)
            ->fetch();
    }

    public function edit($id)
    {
        if ($this->request->isPost()) {

        }
        $data = $this->model->getInfo($id);
        if (!$data) $this->error('该评论已删除或不存在');
        $data = json_decode(json_encode($data, true), true);
        $news_url = url('news/detail', ['id' => $data['news_id']]);
        $data['news_id'] = ("<a href='{$news_url}'>{$data['news_id']}</a>");
        $uid_url = url('users/edit', ['id' => $data['uid']]);
        $data['uid'] = "<a href='{$uid_url}'>{$data['uid']}</a>";
        $pid_url = $data['pid'] ? url('edit', ['id' => $data['pid']]) : 'javascript:;';
        $data['pid'] = "<a href='{$pid_url}'>{$data['pid']}</a>";

        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['static', 'news_id', '资讯id【点击查看资讯详情】', '', [$data['news_id']]],
                ['static', 'uid', '用户id【点击查看用户详情】'],
                ['gallery', 'u_avatar', '用户头像'],
                ['static', 'u_nickname', '用户昵称'],
                ['static', 'pid', '上级评论id【点击查看上级评论】'],
                ['textarea', 'content', '评论内容'],
                ['static', 'comment_num', '评论数'],
                ['static', 'like_num', '点赞数']
            ])
            ->setFormData($data)
            ->hideBtn('submit')
            ->fetch();
    }

    public function delete($ids)
    {
        Db::startTrans();
        $data = $this->model->getList([
            'flag' => 1
        ], 'id asc', 'id,pid');
        $data = json_decode(json_encode($data, true), true);
        $data = getLevelTree($data, $ids, 0);
        $arr = [];
        foreach ($data as $item) {
            array_push($arr, $item['id']);
        }
        $res = $this->model->del($ids);
        if (!$res) {
            Db::rollback();
            $this->error('删除失败');
        }
        $res = $this->model->delAll($arr);
        if (!$res) {
            Db::rollback();
            $this->error('删除失败');
        }
        Db::commit();
        $this->success('删除成功');
    }
}