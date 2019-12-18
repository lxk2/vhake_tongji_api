<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-11
 * Time: 11:25
 */

namespace app\api\home\v1;
use app\api\home\Base;
use app\common\model\News as NewsModel;
use app\common\model\NewsCats as NewsCatsModel;
use app\common\model\UsersClickLikeNews as UsersClickLikeNewsModel;
use app\common\model\UsersLookNews as UsersLookNewsModel;
use think\Db;

class News extends Base
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new NewsModel();
    }

    public function getNewsList()
    {
        $page = $this->post_json['page'] ?: 1;
        $list_rows = $this->post_json['list_rows'] ?: 10;
        $cats_id = $this->post_json['cats_id'] ?: 0;
        $where_sql = <<<ABC
    flag = 1 and status = 1
ABC;
        if ($cats_id) $where_sql .= <<<ABC
    and cats_id = {$cats_id}
ABC;
        else $where_sql .= <<<ABC
    and is_top = 1
ABC;

        $data = $this->model->getList($where_sql, 'is_top desc,sort desc,id desc', 'id,title,cats_id,pic,video_url,duration,look_num,small_appid,is_ad,small_app_path', $page, $list_rows);
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
        $data = $this->model->getInfo($id, [
            'flag' => 1,
            'status' => 1
        ], 'id,cats_id,title,video_url,duration,like_num,content,source,create_time,click_num,pic');
        if (!$data) return $this->jsonErr();
        $data->click_num = $data['click_num'] + 1;

        $data->save();
        $data = json_decode(json_encode($data, true), true);
        getPic($data, 'pic');
        $data['create_time'] = wordTime($data['create_time']);
        $cats_name = (new NewsCatsModel())->getInfo($data['cats_id'], [], 'title');
        $data['cats_name'] = $cats_name ? $cats_name['title'] : '';
        $data['is_click_like'] = (new UsersClickLikeNewsModel())->isClickLike($this->user_info['id'], $data['id']) ? 1 : 0;
        if ($data['video_url']) getPic($data, 'video_url');

        if ($this->user_info) {
            $res = (new UsersLookNewsModel())->createData($this->user_info['id'], $data['id']);
        }

        return $this->jsonOk('操作成功', [
            'info' => $data
        ]);
    }

    /**
     * 播放量
     */
    public function playNum()
    {
        $id = $this->post_json['id'];
        if (!$id) return $this->jsonErr();
        $res = $this->model->addPlayNum($id);
        if ($res) return $this->jsonOk();
        return $this->jsonErr();
    }

    /**
     * 广告点击量
     */
    public function adClickNum()
    {
        $id = $this->post_json['id'];
        if (!$id) return $this->jsonErr();
        $data = $this->model->getInfo($id, [
            'flag' => 1
        ], 'id,click_num');
        if (!$data) return $this->jsonErr();
        $data->click_num = $data['click_num'] + 1;
        $data->save();
        return $this->jsonOk('操作成功');
    }
}