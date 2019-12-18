<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-11
 * Time: 12:26
 */

namespace app\common\model;
class NewsComment extends Base
{
    protected $table = 'vhake_news_comment';

    public function createData($data)
    {
        if ($data['id']) {
            $rows = $this->where([
                'flag' => 1,
                'id' => $data['id']
            ])->update([
                'content' => $data['content'] ?: ''
            ]);
            if ($rows === false) return false;
            return true;
        } else {
            $this->allowField(true)->isUpdate(false)->data([
                'news_id' => $data['news_id'] ?: 0,
                'uid' => $data['uid'] ?: 0,
                'u_avatar' => $data['u_avatar'] ?: '',
                'u_nickname' => $data['u_nickname'] ?: '',
                'pid' => $data['pid'] ?: 0,
                'content' => $data['content'] ?: ''
            ])->save();
            if ($this->id) return $this->id;
            return false;
        }
    }
}