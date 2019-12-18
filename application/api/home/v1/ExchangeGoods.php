<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-14
 * Time: 10:45
 */

namespace app\api\home\v1;
use app\api\home\Base;
use app\common\model\ExchangeGoods as ExchangeGoodsModel;
use think\Db;

class ExchangeGoods extends Base
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ExchangeGoodsModel();
    }

    public function getList()
    {
        $page = $this->post_json['page'] ?: 1;
        $list_rows = $this->post_json['list_rows'] ?: 10;
        $data = $this->model->getList([
            'flag' => 1,
            'status' => 1
        ], 'is_top desc,sort asc,id desc', 'id,title,pic,score', $page, $list_rows);
        $data = json_decode(json_encode($data, true), true);
        getPic($data, 'pic');
        return $this->jsonOk('操作成功', [
            'list' => $data
        ]);
    }

    public function getDetail()
    {
        $id = $this->post_json['id'];
        if (!$id) return $this->jsonErr();
        $data = Db::view('vhake_exchange_goods', 'id,title,pic,num,score,locals_id,end_time')
            ->view('vhake_locals', 'title as locals_title,loop_pic,mobile,map,map_address,content,small_app_name,small_appid,small_app_path,merchant_time,merchant_name', 'vhake_exchange_goods.locals_id = vhake_locals.id', 'LEFT')
            ->where([
                'vhake_exchange_goods.flag' => 1,
                'vhake_exchange_goods.status' => 1,
                'vhake_locals.flag' => 1,
                'vhake_locals.status' => 1,
                'vhake_exchange_goods.id' => $id
            ])
            ->find();
        if (!$data) return $this->jsonErr('该兑换奖品已删除或不存在');
        getPic($data, 'pic');
        getPic($data, 'loop_pic');
        $data['map'] = explode(',', $data['map']);
        return $this->jsonOk('操作成功', [
            'info' => $data
        ]);
    }
}