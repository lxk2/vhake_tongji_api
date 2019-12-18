<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-14
 * Time: 12:47
 */

namespace app\api\admin;

use app\common\builder\ZBuilder;
use think\Request;
use app\common\model\UsersExchangeGoodsCheckIn as UsersExchangeGoodsCheckInModel;
use app\common\model\Locals as LocalsModel;

class UsersExchangeGoodsCheckIn extends Base
{
    protected $model;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new UsersExchangeGoodsCheckInModel();
    }

    protected function getLocals()
    {
        $data = (new LocalsModel())->getList([], 'id desc', 'id,title', null, null);
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
        $data_list = $this->model->getList($map, 'id desc', null, null, null, true, false);
        return ZBuilder::make('table')
            ->setTableName('users_exchange_goods_check_in')
            ->setSearch([
                'id' => 'ID',
                'unique_code' => '唯一码',
                'check_in_mobile' => '手机号'
            ])
            ->addTimeFilter('create_time')
            ->addFilter('ticket', $this->getLocals())
            ->addColumns([
                ['id', 'ID'],
                ['uid', '用户id', 'callback', function ($value) {
                    $url = url('users/edit', ['id' => $value]);
                    return "<a href='{$url}'>{$value}</a>";
                }],
                ['unique_code', '唯一码'],
                ['ticket', '商家id', 'callback', function ($value) {
                    $url = url('locals/detail', ['id' => $value]);
                    return "<a href='{$url}'>{$value}</a>";
                }],
                ['check_in_mobile', '登记手机号'],
                ['end_time', '有效期'],
                ['writer_off_user_id', '勾销用户id', 'callback', function ($value) {
                    if ($value > 0) {
                        $url = url('users/edit', ['id' => $value]);
                        return "<a href='{$url}'>{$value}</a>";
                    } else if ($value == -1) {
                        return '后台勾销';
                    }
                }],
                ['status', '状态', 'status', '', [
                    '1' => '已勾销',
                    '0' => '未使用',
                    '-1' => '已过期',
                    '-2' => '失效'
                ]],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButtons(['add'])
            ->addRightButton('custom', [
                'title' => '勾销',
                'icon' => 'fa fa-fw fa-check',
                'href' => url('writerOff', ['id' => '__id__'])
            ])
            ->addRightButtons(['delete' => ['data-tips' => '删除后无法恢复。']])
            ->setRowList($data_list)
            ->fetch();
    }

    public function writerOff($id)
    {
        $data = $this->model->getInfo($id);
        if (!$data) $this->error('该记录已删除或不存在');
        if ($data['status'] != 0) $this->error('该兑换不能勾销');
        $data->status = 1;
        $data->writer_off_user_id = -1;
        $data->writer_off_time = time();
        $data->save();
        $this->success('勾销成功', 'index');
    }

    public function delete($ids)
    {
        $res = $this->model->del($ids);
        if (!$res) $this->error('删除失败');
        $this->success('删除成功');
    }
}