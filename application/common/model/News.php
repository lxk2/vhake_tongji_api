<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-08
 * Time: 19:32
 */

namespace app\common\model;
class News extends Base
{
    protected $table = 'vhake_news';

    public function createData($data)
    {
        if ($data['id']) {
            $rows = $this->where([
                'flag' => 1,
                'id' => $data['id']
            ])->update([
                'cats_id' => $data['cats_id'] ?: 0,
                'title' => $data['title'] ?: '',
                'pic' => $data['pic'] ?: '',
                'video_url' => $data['video_url'] ?: '',
                'duration' => $data['duration'] ?: '',
                'content' => $data['content'] ?: '',
                'source' => $data['source'] ?: '',
                'sort' => $data['sort'] ?: 0
            ]);
            if ($rows === false) return false;
            return true;
        } else {
            $this->allowField(true)->isUpdate(false)->data([
                'cats_id' => $data['cats_id'] ?: 0,
                'title' => $data['title'] ?: '',
                'pic' => $data['pic'] ?: '',
                'video_url' => $data['video_url'] ?: '',
                'duration' => $data['duration'] ?: '',
                'content' => $data['content'] ?: '',
                'source' => $data['source'] ?: '',
                'sort' => $data['sort'] ?: 0
            ])->save();
            if ($this->id) return $this->id;
            return false;
        }
    }

    /**
     * 增加播放量
     */
    public function addPlayNum($id)
    {
        $data = $this->where([
            'flag' => 1
        ])->field('id,look_num')->find($id);
        if (!$data) return false;
        $data->look_num = $data['look_num'] + 1;
        $data->save();
        return true;
    }

    /**
     * 增加资讯的分享量 he 分享微信好友总量
     */
    public function shareWechatFriendsNum($id)
    {
        $data = $this->where([
            'flag' => 1
        ])->field('share_num,share_wechat_friends_num,id')->find($id);
        if (!$data) return false;
        $data->share_num = $data['share_num'] + 1;
        $data->share_wechat_friends_num = $data['share_wechat_friends_num'] + 1;
        $data->save();
        return true;
    }

    /**
     * 增加资讯的分享量 he 分享朋友圈总量
     */
    public function shareCircleFriendsNum($id)
    {
        $data = $this->where([
            'flag' => 1
        ])->field('id,share_num,share_circle_friends_num')->find($id);
        if (!$data) return false;
        $data->share_num = $data['share_num'] + 1;
        $data->share_circle_friends_num = $data['share_circle_friends_num'] + 1;
        $data->save();
        return true;
    }
}