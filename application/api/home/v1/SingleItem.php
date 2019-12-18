<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-13
 * Time: 15:13
 */

namespace app\api\home\v1;
use app\api\home\Base;
use app\common\model\SingleItem as SingleItemModel;

class SingleItem extends Base
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new SingleItemModel();
    }

    public function getSingleItem()
    {
        $id = $this->post_json['id'];
        if (!$id) return $this->jsonErr();
        $data = $this->model->getInfo($id, [
            'flag' => 1
        ], 'item_value');
        if (!$data) return $this->jsonErr('该记录已删除或不存在');
        return $this->jsonOk('操作成功', [
            'info' => $data
        ]);
    }
}