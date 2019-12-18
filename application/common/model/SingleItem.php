<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-13
 * Time: 14:50
 */

namespace app\common\model;
class SingleItem extends Base
{
    protected $table = 'vhake_single_item';

    public function createData($data)
    {
        if ($data['id']) {
            $rows = $this->where([
                'flag' => 1,
                'id' => $data['id']
            ])->update([
                'item_key' => $data['item_key'] ?: '',
                'item_value' => $data['item_value'] ?: ''
            ]);
            if ($rows === false) return false;
            return true;
        } else {
            $this->allowField(true)->isUpdate(false)->data([
                'item_key' => $data['item_key'] ?: '',
                'item_value' => $data['item_value'] ?: ''
            ])->save();
            if ($this->id) return $this->id;
            return false;
        }
    }
}