<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-14
 * Time: 18:10
 */

namespace app\api\admin;
use app\common\builder\ZBuilder;
use think\Loader;
use think\Request;
use app\common\model\LevelSet as LevelSetModel;

class LevelSet extends Base
{
    protected $model;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new LevelSetModel();
    }

    public function index()
    {
        $map = $this->getMap();
        $map['flag'] = 1;
        $data_list = $this->model->getList($map, 'score asc,id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('level_set')
            ->addColumns([
                ['id', 'ID'],
                ['score', '积分', 'number'],
                ['level_name', '称谓', 'text.edit'],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButton('add')
            ->addRightButtons(['edit', 'delete' => ['data-tips' => '删除后无法恢复。']])
            ->setRowList($data_list)
            ->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = input();
            $validate = Loader::validate('LevelSet');
            if (!$validate->check($data)) $this->error($validate->getError());
            $res = $this->model->createData($data);
            if ($res) $this->success('新增成功', 'index');
            $this->error('新增失败');
        }
        return ZBuilder::make('form')
            ->addFormItems([
                ['number', 'score' ,'积分'],
                ['text', 'level_name', '称谓'],
                ['image', 'level_icon', '图标'],
                ['colorpicker', 'level_color', '字体颜色'],
            ])
            ->fetch();
    }

    public function edit($id)
    {
        if ($this->request->isPost()) {
            $data = input();
            if (!$data['id']) $this->error('数据错误');
            $validate = Loader::validate('LevelSet');
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
                ['number', 'score' ,'积分'],
                ['text', 'level_name', '称谓'],
                ['image', 'level_icon', '图标'],
                ['colorpicker', 'level_color', '字体颜色'],
            ])
            ->setFormData($data)
            ->fetch();
    }

    public function delete($ids)
    {
        $res = $this->model->del($ids);
        if ($res) $this->success('删除成功');
        $this->error('删除失败');
    }
}