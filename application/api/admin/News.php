<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-08
 * Time: 19:31
 */

namespace app\api\admin;

use app\common\builder\ZBuilder;
use think\Loader;
use think\Request;
use app\common\model\News as NewsModel;
use app\common\model\NewsCats as NewsCatsModel;

class News extends Base
{
    protected $model, $cats_data;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new NewsModel();
        $this->cats_data = $this->getCats();
    }

    public function index()
    {
        $map = $this->getMap();
        $map['flag'] = 1;
        $map['is_ad'] = 0;
        $data_list = $this->model->getList($map, 'is_top desc,id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('news')
            ->setSearch([
                'id' => 'ID',
                'title' => '标题',
                'source' => '来源'
            ])
            ->addOrder('click_num,look_num,like_num,comment_num,share_num,share_circle_friends_num,share_wechat_friends_num')
            ->setColumnWidth([
                'share_wechat_friends_num' => 150,
                'share_circle_friends_num' => 150,
                'like_num' => 150,
                'look_num' => 150,
                'title' => 200,
                'source' => 150
            ])
            ->addTimeFilter('create_time')
            ->addFilter('cats_id', $this->cats_data)
            ->addColumns([
                ['id', 'ID'],
                ['cats_id', '所属分类', 'callback', function ($value) {
                    return $this->cats_data[$value];
                }],
                ['title', '标题', 'text.edit'],
                ['source', '来源/作者'],
                ['click_num', '点击量'],
                ['look_num', '视频播放量'],
                ['like_num', '文章点赞量'],
                ['comment_num', '评论量'],
                ['share_num', '分享总量'],
                ['share_circle_friends_num', '分享海报总量'],
                ['share_wechat_friends_num', '分享微信好友总量'],
                ['sort', '排序', 'number'],
                ['is_top', '是否推荐', 'switch'],
                ['status', '状态', 'switch'],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButtons(['add'])
            ->addRightButtons(['edit', 'delete' => ['data-tips' => '删除后无法恢复。']])
            ->setRowList($data_list)
            ->fetch();
    }

    public function adList()
    {
        $map = $this->getMap();
        $map['flag'] = 1;
        $map['is_ad'] = 1;
        $data_list = $this->model->getList($map, 'is_top desc,id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('news')
            ->setSearch([
                'id' => 'ID',
                'title' => '标题',
                'small_app_name' => '小程序名称'
            ])
            ->addTimeFilter('create_time')
            ->addFilter('cats_id', $this->cats_data)
            ->addColumns([
                ['id', 'ID'],
                ['title', '标题', 'text.edit'],
                ['small_app_name', '小程序名称'],
                ['small_appid', 'appid'],
                ['small_app_path', '跳转页面路径'],
                ['click_num', '点击量'],
                ['sort', '排序', 'number'],
                ['status', '状态', 'switch'],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButton('custom', [
                'title' => '新增广告',
                'href' => url('addAd')
            ])
            ->addRightButton('custom', [
                'title' => '修改',
                'icon' => 'fa fa-fw fa-pencil',
                'href' => url('editAd', ['id' => '__id__'])
            ])
            ->addRightButtons(['delete' => ['data-tips' => '删除后无法恢复。']])
            ->setRowList($data_list)
            ->fetch();
    }

    public function addAd()
    {
        if ($this->request->isPost()) {
            $data = input();
            if (!$data['title']) $this->error('请输入标题');
            if (!$data['pic']) $this->error('请上传图片');
            if (!$data['small_app_name']) $this->error('请输入小程序名称');
            if (!$data['small_appid']) $this->error('请输入appid');
            $data['is_top'] = 1;
            $data['cats_id'] = 0;
            $data['is_ad'] = 1;
            $this->model->allowField(true)->isUpdate(false)->data($data)->save();
            if ($this->model->id)
                $this->success('新增成功', 'adList');
            $this->error('新增失败');
        }
        return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'title', '标题'],
                ['images', 'pic', '封面图'],
                ['text', 'small_app_name', '小程序名称'],
                ['text', 'small_appid', 'appid'],
                ['text', 'small_app_path', '页面路径'],
                ['number', 'sort', '排序']
            ])
            ->fetch();
    }

    public function editAd($id)
    {
        if ($this->request->isPost()) {
            $data = input();
            if (!$data['id']) $this->error('数据错误');
            if (!$data['title']) $this->error('请输入标题');
            if (!$data['pic']) $this->error('请上传图片');
            if (!$data['small_app_name']) $this->error('请输入小程序名称');
            if (!$data['small_appid']) $this->error('请输入appid');
            $res = $this->model->where([
                'flag' => 1,
                'id' => $data['id']
            ])->update([
                'title' => $data['title'],
                'pic' => $data['pic'],
                'small_app_name' => $data['small_app_name'],
                'small_appid' => $data['small_appid'],
                'small_app_path' => $data['small_app_path'],
                'sort' => $data['sort']
            ]);
            if ($res === false) $this->error('修改失败');
            $this->success('修改成功', 'adList');
        }
        $data = $this->model->getInfo($id);
        if (!$data) $this->error('该记录已删除或不存在');
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['text', 'title', '标题'],
                ['images', 'pic', '封面图'],
                ['text', 'small_app_name', '小程序名称'],
                ['text', 'small_appid', 'appid'],
                ['text', 'small_app_path', '页面路径'],
                ['number', 'sort', '排序']
            ])
            ->setFormData($data)
            ->fetch();
    }

    protected function getCats()
    {
        $model = new NewsCatsModel();
        $data = $model->getList([
            'flag' => 1,
            'status' => 1
        ], 'sort asc,id desc', 'id,title', null, null);
        $arr = [];
        foreach ($data as $item) {
            $arr[$item['id']] = $item['title'];
        }
        return $arr;
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = input();
            $validate = Loader::validate('News');
            if (!$validate->check($data)) $this->error($validate->getError());
            $res = $this->model->createData($data);
            if ($res) $this->success('新增成功', 'index');
            $this->error('新增失败');
        }
        return ZBuilder::make('form')
            ->addFormItems([
                ['select', 'cats_id', '所属分类', '', $this->cats_data],
                ['text', 'title', '标题'],
                ['images', 'pic', '封面图'],
                ['file', 'video_url', '视频'],
                ['text', 'duration', '时长'],
                ['ueditor', 'content', '内容'],
                ['text', 'source', '来源/作者'],
                ['number', 'sort', '排序']
            ])
            ->fetch();
    }

    public function edit($id)
    {
        if ($this->request->isPost()) {
            $data = input();
            if (!$data['id']) $this->error('数据错误');
            $validate = Loader::validate('News');
            if (!$validate->check($data)) $this->error($validate->getError());
            $res = $this->model->createData($data);
            if ($res) $this->success('修改成功', 'index');
            $this->error('修改失败');
        }
        $data = $this->model->getInfo($id, [
            'flag' => 1
        ]);
        if (!$data) $this->error('该资讯已删除或不存在');
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['select', 'cats_id', '所属分类', '', $this->cats_data],
                ['text', 'title', '标题'],
                ['images', 'pic', '封面图'],
                ['file', 'video_url', '视频'],
                ['text', 'duration', '时长'],
                ['ueditor', 'content', '内容'],
                ['text', 'source', '来源/作者'],
                ['number', 'sort', '排序']
            ])
            ->setFormData($data)
            ->fetch();
    }

    public function detail($id)
    {
        $data = $this->model->getInfo($id, [
            'flag' => 1
        ]);
        if (!$data) $this->error('该资讯已删除或不存在');
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['select', 'cats_id', '所属分类', '', $this->cats_data],
                ['text', 'title', '标题'],
                ['images', 'pic', '封面图'],
                ['file', 'video_url', '视频'],
                ['text', 'duration', '时长'],
                ['ueditor', 'content', '内容'],
                ['text', 'source', '来源/作者'],
                ['number', 'sort', '排序']
            ])
            ->setFormData($data)
            ->hideBtn('submit')
            ->fetch();
    }

    public function delete($ids)
    {
        $rows = $this->model->del($ids);
        if ($rows === false) $this->error('删除失败');
        $this->success('删除成功');
    }
}