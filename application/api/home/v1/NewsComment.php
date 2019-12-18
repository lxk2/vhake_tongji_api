<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-11
 * Time: 15:42
 */

namespace app\api\home\v1;

use app\api\home\Base;
use app\common\model\NewsComment as NewsCommentModel;
use app\common\model\UsersClickLikeComment as UsersClickLikeCommentModel;

class NewsComment extends Base
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new NewsCommentModel();
    }

    public function getCommentList()
    {
        $news_id = $this->post_json['news_id'] ?: 0;
        $pid = $this->post_json['pid'] ?: 0;
        $page = $this->post_json['page'] ?: 1;
        $list_rows = $this->post_json['list_rows'] ?: 10;
        if (!$news_id) return $this->jsonErr();
        $where_sql = <<<ABC
    flag  = 1 and status = 1 and news_id = {$news_id}
ABC;

        if ($pid) $where_sql .= <<<ABC
    and pid = {$pid}
ABC;
        else $where_sql .= <<<ABC
    and pid = 0
ABC;

        $data = $this->model->getList($where_sql, 'id desc', 'id,uid,u_avatar,u_nickname,content,like_num,create_time,comment_num', $page, $list_rows);
        $data = json_decode(json_encode($data, true), true);
        $user_click_like_comment_model = new UsersClickLikeCommentModel();
        foreach ($data as &$item) {
            $item['create_time'] = date('m-d H:i:s', $item['create_time']);
            $item['is_click_like'] = $user_click_like_comment_model->isClickLike($this->user_info['id'], $item['id']) ? 1 : 0;
            $item['level_name'] = $this->getUserLevelName($item['uid']) ?: '';
        }
        return $this->jsonOk('操作成功', [
            'list' => $data
        ]);
    }

    public function getCommentDetail()
    {
        $id = $this->post_json['id'] ?: 0;
        if (!$id) return $this->jsonErr();
        $data = $this->model->getInfo($id, [
            'flag' => 1,
            'status' => 1
        ], 'id,news_id,uid,u_avatar,u_nickname,pid,content,comment_num,like_num,create_time');
        if (!$data) return $this->jsonErr('该评论已删除或不存在');
        $data = json_decode(json_encode($data, true), true);
        $data['create_time'] = date('m-d H:i:s', $data['create_time']);
        $data['is_click_like'] = (new UsersClickLikeCommentModel())->isClickLike($this->user_info['id'], $data['id']) ? 1 : 0;
        $data['level_name'] = $this->getUserLevelName($data['uid']);
        return $this->jsonOk('操作成功', [
            'info' => $data
        ]);
    }
}