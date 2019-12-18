<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-04-29
 * Time: 00:27
 */

namespace app\common\model;
class SendPostStatistics extends Base
{
    protected $table = 'vhake_send_post_statistics';

    public function createData($data)
    {
        if ($data['id']) {
            return false;
        } else {
            $this->allowField(true)->isUpdate(false)->data([
                'news_id' => $data['news_id'] ?: 0,
                'send_num' => $data['send_num'] ?: 0
            ])->save();
            if ($this->id) return $this->id;
            return false;
        }
    }
}