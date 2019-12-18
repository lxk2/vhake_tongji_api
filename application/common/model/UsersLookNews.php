<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-21
 * Time: 17:07
 */

namespace app\common\model;
class UsersLookNews extends Base
{
    protected $table = 'vhake_users_look_news';

    public function createData($uid, $news_id)
    {
        $data = $this->where([
            'flag' => 1,
            'uid' => $uid,
            'news_id' => $news_id
        ])->field('id,num')->find();
        if ($data) {
            $data->num = $data['num'] + 1;
            $data->save();
            return true;
        } else {
            $this->allowField(true)->isUpdate(false)->data([
                'uid' => $uid,
                'news_id' => $news_id,
                'num' => 1
            ])->save();
            if ($this->id) return $this->id;
            return false;
        }
    }
}