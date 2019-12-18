<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-26
 * Time: 11:59
 */

namespace app\api\admin;

use app\api\home\v1\Wx;
use app\common\builder\ZBuilder;
use think\Db;
use think\Request;
use app\common\model\News as NewsModel;
use app\common\model\Formid as FormidModel;
use app\common\model\Users as UsersModel;
use app\common\model\SendPostStatistics as SendPostStatisticsModel;

class SendPost extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    protected function getNews()
    {
        $data = (new NewsModel())->getList([
            'flag' => 1,
            'status' => 1,
            'is_ad' => 0
        ], 'id desc', 'id,title');
        $arr = [];
        foreach ($data as $item) {
            $arr[$item['id']] = $item['title'];
        }
        return $arr;
    }

    public function sendPost()
    {
        if ($this->request->isPost()) {
            $news_id = input('news_id');
            if (!$news_id) $this->error('请选择资讯');
            $this->success('选择成功', url('sendPostNext', ['id' => $news_id]));
        }
        return ZBuilder::make('form')
            ->addFormItems([
                ['select', 'news_id', '资讯', '', $this->getNews()],
            ])
            ->fetch();
    }

    public function sendPostNext($id)
    {
        if ($this->request->isPost()) {
            $data = input();
            if (!$data['id']) $this->error('数据错误');
            if (!$data['title']) $this->error('请输入标题');
            if (!$data['cats_title']) $this->error('请输入类型');
            if (!$data['source']) $this->error('请输入创建者');
            if (!$data['create_time']) $this->error('请输入创建时间');

            $user_list = (new UsersModel())->getList([
                'flag' => 1
            ], 'id desc', 'id,small_openid');
            $user_list = json_decode(json_encode($user_list, true), true);

            $wx = new Wx();
            $form_model = new FormidModel();
            $arr = [];
            foreach ($user_list as $item) {
                $formid = $form_model->getFormIdByUserId($item['id']);
                if ($formid) {
                    $res = $wx->sendTemplateMessage($item['small_openid'], config('template_id')['report_status_notification'], $formid['formid'], [
                        'keyword1' => [
                            'value' => $data['title']
                        ],
                        'keyword2' => [
                            'value' => $data['cats_title']
                        ],
                        'keyword3' => [
                            'value' => $data['summary'] ?: ''
                        ],
                        'keyword4' => [
                            'value' => $data['source']
                        ],
                        'keyword5' => [
                            'value' => $data['create_time']
                        ]
                    ], 'pages/index/detail?id=' . $data['id']);
                    if ($res['errcode'] == 0) {
                        array_push($arr, $formid['id']);
                    }
                }

            }
            $rows = $form_model->where([
                'flag' => 1,
                'id' => ['in', $arr]
            ])->update([
                'flag' => -1
            ]);

            // TODO 记录发送了几个
            $sends = (new SendPostStatisticsModel())->createData([
                'news_id' => $data['id'],
                'send_num' => $rows
            ]);

            $this->success('发送成功', 'sendPost');
        }
        $data = Db::view('vhake_news', 'id,title,source,create_time')
            ->view('vhake_news_cats', 'title as cats_title', 'vhake_news.cats_id = vhake_news_cats.id', 'LEFT')
            ->where([
                'vhake_news.flag' => 1,
                'vhake_news.status' => 1,
                'vhake_news_cats.flag' => 1,
                'vhake_news_cats.status' => 1,
                'vhake_news.id' => $id
            ])
            ->find();
        if (!$data) $this->error('该资讯已删除或不存在');
        $data['create_time'] = date('Y-m-d H:i:s', $data['create_time']);
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['text', 'title', '标题'],
                ['static', 'cats_title', '类型', '', $data['cats_title'], true],
                ['text', 'summary', '摘要'],
                ['text', 'source', '创建者'],
                ['datetime', 'create_time', '创建时间'],
            ])
            ->setFormData($data)
            ->fetch();
    }

    public function index()
    {
        $map = $this->getMap();
        $map['flag'] = 1;
        $data_list = (new SendPostStatisticsModel())->getList($map, 'id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('send_post_statistics')
            ->setSearch([
                'news_id' => '资讯ID'
            ])
            ->addTimeFilter('create_time')
            ->addColumns([
                ['id', 'ID'],
                ['news_id', '资讯id', 'callback', function ($value) {
                    $url = url('news/detail', ['id' => $value]);
                    $title = (new NewsModel())->getInfo($value, [

                    ], 'title')['title'] ?: '';
                    return "<a href='{$url}'>{$value} - {$title}</a>";
                }],
                ['send_num', '消息送达人数'],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addRightButtons(['delete' => ['data-tips' => '删除后无法恢复。']])
            ->setRowList($data_list)
            ->fetch();
    }
}