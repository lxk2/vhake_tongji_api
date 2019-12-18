<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-12
 * Time: 14:15
 */

namespace app\common\model;
class ShareAppMessage extends Base
{
    protected $model;

    public function createData($data)
    {
        if ($data['id']) {
            $rows = $this->where([
                'flag' => 1,
                'id' => $data['id']
            ])->update([
                'title' => $data['title'] ?: '',
                'pic' => $data['pic'] ?: ''
            ]);
            if ($rows === false) return false;
            return true;
        } else {
            $this->allowField(true)->isUpdate(false)->data([
                'title' => $data['title'] ?: '',
                'pic' => $data['pic'] ?: ''
            ])->save();
            if ($this->id)
                return $this->id;
            return false;
        }
    }

    public function getShareInfo($id)
    {
        $data = $this->getInfo($id, [], 'id,title,pic');
        if (!$data) return false;
        $data = json_decode(json_encode($data, true), true);
        getPic($data, 'pic');
        return $data;
    }
}