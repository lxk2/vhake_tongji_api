<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-13
 * Time: 23:02
 */

namespace app\api\admin;
use app\common\builder\ZBuilder;
use think\Loader;
use think\Request;
use app\common\model\ExchangeGoods as ExchangeGoodsModel;
use app\common\model\Locals as LocalsModel;

class ExchangeGoods extends Base
{
    protected $model, $locals_list;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ExchangeGoodsModel();
        $this->locals_list = $this->getLocals();
    }

    protected function getLocals()
    {
        $model = new LocalsModel();
        $data = $model->getList([
            'flag' => 1,
            'status' => 1
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
        $data_list = $this->model->getList($map, 'is_top desc,sort asc,id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('exchange_goods')
            ->setSearch([
                'id' => 'ID',
                'title' => '标题'
            ])
            ->addTimeFilter('create_time')
            ->addFilter('locals_id', $this->locals_list)
            ->addColumns([
                ['id', 'ID'],
                ['locals_id', '商家id', 'callback', function ($value) {
                    $url = url('Locals/detail', ['id' => $value]);
                    return "<a href='{$url}'>{$value}</a>";
                }],
                ['title', '标题', 'text.edit'],
                ['pic', '封面图', 'picture'],
                ['num', '数量', 'number'],
                ['sort', '排序', 'number'],
                ['is_top', '是否置顶', 'switch'],
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
            $validate = Loader::validate('ExchangeGoods');
            if (!$validate->check($data)) $this->error($validate->getError());
            $res = $this->model->createData($data);
            if ($res) $this->success('新增成功', 'index');
            $this->error('新增失败');
        }
        return ZBuilder::make('form')
            ->addFormItems([
                ['select', 'locals_id', '所属商家', '', $this->locals_list],
                ['text', 'title', '标题'],
                ['image', 'pic', '封面图'],
                ['number', 'score', '所需积分'],
                ['number', 'num', '数量'],
                ['datetime', 'end_time', '有效期'],
                ['number', 'sort', '排序']
            ])
            ->fetch();
    }

    public function edit($id)
    {
        if ($this->request->isPost()) {
            $data = input();
            if (!$data['id']) $this->error('数据错误');
            $validate = Loader::validate('ExchangeGoods');
            if (!$validate->check($data)) $this->error($validate->getError());
            $res = $this->model->createData($data);
            if ($res) $this->success('修改成功', 'index');
            $this->error('修改失败');
        }
        $data = $this->model->getInfo($id);
        if (!$data) $this->error('该记录已删除或不存在');
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['select', 'locals_id', '所属商家', '', $this->locals_list],
                ['text', 'title', '标题'],
                ['image', 'pic', '封面图'],
                ['number', 'score', '所需积分'],
                ['number', 'num', '数量'],
                ['datetime', 'end_time', '有效期'],
                ['number', 'sort', '排序']
            ])
            ->setFormData($data)
            ->fetch();
    }

    public function detail($id)
    {
        $data = $this->model->getInfo($id);
        if (!$data) $this->error('该记录已删除或不存在');
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['select', 'locals_id', '所属商家', '', $this->locals_list],
                ['static', 'title', '标题'],
                ['gallery', 'pic', '封面图'],
                ['static', 'score', '所需积分'],
                ['static', 'num', '数量'],
                ['static', 'end_time', '有效期'],
                ['static', 'sort', '排序']
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
}