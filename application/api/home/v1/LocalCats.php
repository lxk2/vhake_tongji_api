<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-12
 * Time: 16:40
 */

namespace app\api\home\v1;
use app\api\home\Base;
use app\common\model\LocalCats as LocalCatsModel;

class LocalCats extends Base
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new LocalCatsModel();
    }

    /**
     * 获取分类
     */
    public function getCats()
    {
        $data = $this->model->getList([
            'flag' => 1,
            'status' => 1
        ], 'sort asc,id desc', 'id,title,status', null, null);
        $data = json_decode(json_encode($data, true), true);
        array_unshift($data, [
            'id' => 0,
            'title' => '推荐',
            'status' => 0
        ]);
        return $this->jsonOk('操作成功', [
            'list' => $data
        ]);
    }
}