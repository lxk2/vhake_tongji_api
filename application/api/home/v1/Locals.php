<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-12
 * Time: 18:01
 */

namespace app\api\home\v1;
use app\api\home\Base;
use app\common\model\Locals as LocalsModel;

class Locals extends Base
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new LocalsModel();
    }

    public function getList()
    {
        $cats_id = $this->post_json['cats_id'] ?: 0;
        $page = $this->post_json['page'] ?: 1;
        $list_rows = $this->post_json['list_rows'] ?: 10;
        $where_sql = <<<ABC
    flag = 1 and status = 1
ABC;

        if ($cats_id) {
            $where_sql .= <<<ABC
    and cats_id = {$cats_id}
ABC;

        } else {
            $where_sql .= <<<ABC
    and is_top = 1
ABC;

        }
        $data = $this->model->getList($where_sql, 'is_top desc,sort asc,id desc', 'id,cats_id,title,pic', $page, $list_rows);
        $data = json_decode(json_encode($data, true), true);
        getPic($data, 'pic');
        return $this->jsonOk('操作成功', [
            'list' => $data
        ]);
    }

    public function getDetail()
    {
        $id = $this->post_json['id'] ?: 2;
        if (!$id) return $this->jsonErr();
        $data = $this->model->getInfo($id, [
            'flag' => 1,
            'status' => 1
        ], 'id,title,loop_pic,mobile,map,map_address,content,click_num,pic,small_app_name,small_appid,small_app_path,merchant_name,merchant_time');
        if (!$data) return $this->jsonErr('该记录已删除或不存在');
        $data->click_num = $data['click_num'] + 1;
        $data->save();
        $data = json_decode(json_encode($data, true), true);
        getPic($data, 'loop_pic');
        getPic($data, 'pic');
        $data['map'] = explode(',', $data['map']);
        $result = bd_decrypt($data['map'][0], $data['map'][1]); // BD-09 2 GCJ-02
        $data['map'] = [
            $result['gg_lon'],
            $result['gg_lat']
        ];
        return $this->jsonOk('操作成功', [
            'info' => $data
        ]);
    }

    /**
     * 增加小程序跳转量
     */
    public function addSmallAppJumpNum()
    {
        $id = $this->post_json['id'];
        if (!$id) return $this->jsonErr();
        $data = $this->model->getInfo($id, [
            'flag' => 1
        ], 'id,small_app_jump_num');
        if (!$data) return $this->jsonErr();
        $data->small_app_jump_num = $data['small_app_jump_num'] + 1;
        $data->save();
        return $this->jsonOk('操作成功');
    }

    /**
     * 增加联系商家量
     */
    public function addContactMobileNum()
    {
        $id = $this->post_json['id'];
        if (!$id) return $this->jsonErr();
        $data = $this->model->getInfo($id, [
            'flag' => 1
        ], 'id,contact_mobile_num');
        if (!$data) return $this->jsonErr();
        $data->contact_mobile_num = $data['contact_mobile_num'] + 1;
        $data->save();
        return $this->jsonOk('操作成功');
    }

    /**
     * 增加打开地图量
     */
    public function addOpenMapNum()
    {
        $id = $this->post_json['id'];
        if (!$id) return $this->jsonErr();
        $data = $this->model->getInfo($id, [
            'flag' => 1
        ], 'id,open_map_num');
        if (!$data) return $this->jsonErr();
        $data->open_map_num = $data['open_map_num'] + 1;
        $data->save();
        return $this->jsonOk();
    }
}