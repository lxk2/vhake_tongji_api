<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-14
 * Time: 14:54
 */

namespace app\common\model;
class ManuscriptBox extends Base
{
    protected $table = 'vhake_manuscript_box';

    public function createData($data)
    {
        if ($data['id']) {
            $rows = $this->where([
                'flag' => 1,
                'id' => $data['id']
            ])->update([
                'title' => $data['title'] ?: '',
                'pic' => $data['pic'] ?: '',
                'video_url' => $data['video_url'] ?: '',
                'content' => $data['content'] ?: ''
            ]);
            if ($rows === false) return false;
            return true;
        } else {
            $this->allowField(true)->isUpdate(false)->data([
                'uid' => $data['uid'] ?: '',
                'title' => $data['title'] ?: '',
                'pic' => $data['pic'] ?: '',
                'video_url' => $data['video_url'] ?: '',
                'content' => $data['content'] ?: ''
            ])->save();
            if ($this->id) return $this->id;
            return false;
        }
    }
}