<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-14
 * Time: 16:16
 */

namespace app\api\home\v1;
use app\common\model\ManuscriptBox as ManuscriptBoxModel;
use think\Db;

class ManuscriptBox extends Auth
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ManuscriptBoxModel();
    }

    public function submit()
    {
        Db::startTrans();
        $title = $this->post_json['title'] ?: '';
        $pic = $this->post_json['pic'] ?: [];
        $video_url = $this->post_json['video_url'] ?: '';
        $content = $this->post_json['content'] ?: '';
        if (!$title) return $this->jsonErr('请输入标题');
        if (!$content) return $this->jsonErr('请输入内容');
        $res = $this->model->createData([
            'uid' => $this->user_info['id'],
            'title' => $title,
            'pic' => $pic ? implode(',', $pic) : '',
            'video_url' => $video_url,
            'content' => $content
        ]);
        if ($res) {
            $this->user_info->submiss_num = $this->user_info['submiss_num'] + 1; // 增加投稿数
            $this->user_info->save();
            Db::commit();
            return $this->jsonOk('投稿成功');
        }
        return $this->jsonErr('投稿失败');
    }
}