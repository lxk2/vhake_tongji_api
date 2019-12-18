<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-12
 * Time: 17:20
 */

namespace app\api\admin;

use app\common\builder\ZBuilder;
use think\Loader;
use think\Request;
use app\common\model\Locals as LocalsModel;
use app\common\model\LocalCats as LocalCatsModel;

class Locals extends Base
{
    protected $model, $cats_data;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new LocalsModel();
        $this->cats_data = $this->getCats();
    }

    protected function getCats()
    {
        $model = new LocalCatsModel();
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

    public function index()
    {
        $map = $this->getMap();
        $map['flag'] = 1;
        $data_list = $this->model->getList($map, 'is_top desc,id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('locals')
            ->setSearch([
                'id' => 'ID',
                'title' => '标题',
                'mobile' => '联系电话',
                'map_address' => '详细地址'
            ])
            ->addTimeFilter('create_time')
            ->addOrder('open_map_num,contact_mobile_num,small_app_jump_num,share_wechat_num,share_poster_num')
            ->setColumnWidth([
                'title' => 200,
                'map_address' => 200,
                'open_map_num' => 150,
                'contact_mobile_num' => 150,
                'small_app_jump_num' => 150,
                'share_wechat_num' => 150,
                'share_poster_num' => 150
            ])
            ->addFilter('cats_id', $this->cats_data)
            ->addColumns([
                ['id', 'ID'],
                ['cats_id', '所属分类', 'callback', function ($value) {
                    return $this->cats_data[$value];
                }],
                ['title', '标题', 'text.edit'],
                ['pic', '封面图', 'picture'],
                ['mobile', '联系电话'],
                ['map_address', '详细地址'],
                ['click_num', '点击量'],
                ['open_map_num', '打开地图量'],
                ['contact_mobile_num', '联系商家量'],
                ['small_app_jump_num', '小程序跳转量'],
                ['share_wechat_num', '分享到微信好友量'],
                ['share_poster_num', '分享海报量'],
                ['sort', '排序', 'number'],
                ['is_top', '是否推荐', 'switch'],
                ['status', '状态', 'switch'],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButtons(['add'])
            ->addRightButton('custom', [
                'title' => '查看详情',
                'icon' => 'fa fa-fw fa-mouse-pointer',
                'href' => url('detail', ['id' => '__id__'])
            ])
            ->addRightButtons(['edit', 'delete' => ['data-tips' => '删除后无法恢复。']])
            ->setRowList($data_list)
            ->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = input();
            $validate = Loader::validate('Locals');
            if (!$validate->check($data)) $this->error($validate->getError());
            $res = $this->model->createData($data);
            if ($res) $this->success('新增成功', 'index');
            $this->error('新增失败');
        }
        return ZBuilder::make('form')
            ->addFormItems([
                ['select', 'cats_id', '所属分类', '', $this->cats_data],
                ['text', 'title', '标题'],
                ['image', 'pic', '封面图'],
                ['images', 'loop_pic', '轮播图'],
                ['text', 'mobile', '联系电话'],
                ['text', 'small_app_name', '商家小程序名称', '非必填'],
                ['text', 'small_appid', '商家小程序appid', '非必填'],
                ['text', 'small_app_path', '商家小程序页面路径', '不填默认为跳转到对方小程序首页'],
                ['text', 'merchant_name', '商家名称'],
                ['text', 'merchant_time', '营业时间'],
                ['bmap', 'map', '地图', 'B41DLxfixm3XfjbM7tEMuGRUxSUAk4HB'],
                ['ueditor', 'content', '内容'],
                ['number', 'sort', '排序']
            ])
            ->fetch();
    }

    public function edit($id)
    {
        if ($this->request->isPost()) {
            $data = input();
            $validate = Loader::validate('Locals');
            if (!$validate->check($data)) $this->error($validate->getError());
            $res = $this->model->createData($data);
            if ($res) $this->success('修改成功', 'index');
            $this->error('修改失败');
        }
        $data = $this->model->getInfo($id, [
            'flag' => 1
        ]);
        if (!$data) $this->error('该记录已删除或不存在');
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['select', 'cats_id', '所属分类', '', $this->cats_data],
                ['text', 'title', '标题'],
                ['image', 'pic', '封面图'],
                ['images', 'loop_pic', '轮播图'],
                ['text', 'mobile', '联系电话'],
                ['text', 'small_app_name', '商家小程序名称', '非必填'],
                ['text', 'small_appid', '商家小程序appid', '非必填'],
                ['text', 'small_app_path', '商家小程序页面路径', '不填默认为跳转到对方小程序首页'],
                ['text', 'merchant_name', '商家名称'],
                ['text', 'merchant_time', '营业时间'],
                ['bmap', 'map', '地图', 'B41DLxfixm3XfjbM7tEMuGRUxSUAk4HB', '', $data['map'], $data['map_address']],
                ['ueditor', 'content', '内容'],
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
        if (!$data) $this->error('该记录已删除或不存在');
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['select', 'cats_id', '所属分类', '', $this->cats_data],
                ['static', 'title', '标题'],
                ['gallery', 'pic', '封面图'],
                ['gallery', 'loop_pic', '轮播图'],
                ['static', 'mobile', '联系电话'],
                ['static', 'small_app_name', '商家小程序名称', '非必填'],
                ['static', 'small_appid', '商家小程序appid', '非必填'],
                ['static', 'small_app_path', '商家小程序页面路径', '不填默认为跳转到对方小程序首页'],
                ['static', 'merchant_name', '商家名称'],
                ['static', 'merchant_time', '营业时间'],
                ['bmap', 'map', '地图', 'B41DLxfixm3XfjbM7tEMuGRUxSUAk4HB', '', $data['map'], $data['map_address']],
                ['ueditor', 'content', '内容'],
                ['static', 'sort', '排序']
            ])
            ->setFormData($data)
            ->hideBtn('submit')
            ->fetch();
    }

    public function delete($ids)
    {
        $res = $this->model->del($ids);
        if ($res === false) $this->error('删除失败');
        $this->success('删除成功');
    }
}