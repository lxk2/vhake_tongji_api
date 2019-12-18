<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-21
 * Time: 19:46
 */

namespace app\common\model;
class UsersShareLog extends Base
{
    protected $table = 'vhake_users_share_log';

    public function createData($data)
    {
        $this->allowField(true)->isUpdate(false)->data([
            'uid' => $data['uid'] ?: 0,
            'share_type' => $data['share_type'] ?: 0,
            'news_id' => $data['news_id'] ?: 0,
            'local_id' => $data['local_id'] ?: 0,
            'type_desc' => $data['type_desc'] ?: '',
            'score' => $data['score'] ?: 0
        ])->save();
        if ($this->id) return $this->id;
        return false;
    }
}