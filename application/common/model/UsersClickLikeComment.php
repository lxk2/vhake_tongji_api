<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-11
 * Time: 16:16
 */

namespace app\common\model;
class UsersClickLikeComment extends Base
{
    protected $table = 'vhake_users_click_like_comment';

    /**
     * 点赞与取消点赞
     */
    public function clickLike($uid, $comment_id)
    {
        $data = $this->where([
            'uid' => $uid,
            'comment_id' => $comment_id
        ])->field('id,flag')->find();
        if ($data) {
            if ($data['flag'] === 1) { // 取消点赞
                $data->flag = -1;
                $data->save();
                return 0;
            } else { // 点赞
                $data->flag = 1;
                $data->save();
                return 1;
            }
        } else {
            $this->allowField(true)->isUpdate(false)->data([
                'uid' => $uid,
                'comment_id' => $comment_id
            ])->save();
            if ($this->id) return 1;
            return false;
        }
    }

    /**
     * 查询用户是否有点赞
     */
    public function isClickLike($uid, $comment_id)
    {
        if (!$uid) return false;
        $data = $this->where([
            'flag' => 1,
            'uid' => $uid,
            'comment_id' => $comment_id
        ])->field('id')->find();
        if ($data) return $data['id'];
        return false;
    }
}