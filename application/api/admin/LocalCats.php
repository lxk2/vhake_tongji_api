<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-12
 * Time: 16:31
 */

namespace app\api\admin;
use app\common\builder\ZBuilder;
use think\Request;
use think\Loader;
use app\common\model\LocalCats as LocalCatsModel;

class LocalCats extends Base
{
    protected $model;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new LocalCatsModel();
    }

    public function index()
    {
        $map = $this->getMap();
        $map['flag'] = 1;
        $data_list = $this->model->getList($map, 'sort asc,id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('local_cats')
            ->addColumns([
                ['id', 'ID'],
                ['title', '分类名称', 'text.edit'],
                ['sort', '排序', 'number'],
                ['status', '状态', 'switch'],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButtons(['add'])
            ->addRightButtons(['edit', 'delete' => ['data-tips' => '删除后无法恢复。']])
            ->setRowList($data_list)
            ->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = input();
            $validate = Loader::validate('LocalCats');
            if (!$validate->check($data)) $this->error($validate->getError());
            $res = $this->model->createData($data);
            if ($res) $this->success('新增成功', 'index');
            $this->error('新增失败');
        }
        return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'title', '分类名称'],
                ['number', 'sort', '排序']
            ])
            ->fetch();
    }

    public function edit($id)
    {
        if ($this->request->isPost()) {
            $data = input();
            if (!$data['id']) $this->error('数据错误');
            $validate = Loader::validate('LocalCats');
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
                ['text', 'title', '分类名称'],
                ['number', 'sort', '排序']
            ])
            ->setFormData($data)
            ->fetch();
    }

    public function delete($ids)
    {
        $rows = $this->model->del($ids);
        if ($rows === false) $this->error('删除失败');
        $this->success('删除成功');
    }
}