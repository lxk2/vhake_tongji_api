<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-13
 * Time: 22:52
 */

namespace app\common\model;
class ExchangeGoods extends Base
{
    protected $table = 'vhake_exchange_goods';

    public function createData($data)
    {
        if ($data['id']) {
            $rows = $this->where([
                'flag' => 1,
                'id' => $data['id']
            ])->update([
                'locals_id' => $data['locals_id'] ?: 0,
                'score' => $data['score'] ?: 0,
                'title' => $data['title'] ?: '',
                'pic' => $data['pic'] ?: '',
                'num' => $data['num'] ?: 0,
                'sort' => $data['sort'] ?: 0,
                'end_time' => $data['end_time'] ?: ''
            ]);
            if ($rows === false) return false;
            return true;
        } else {
            $this->allowField(true)->isUpdate(false)->data([
                'locals_id' => $data['locals_id'] ?: 0,
                'score' => $data['score'] ?: 0,
                'title' => $data['title'] ?: '',
                'pic' => $data['pic'] ?: '',
                'num' => $data['num'] ?: 0,
                'sort' => $data['sort'] ?: 0,
                'end_time' => $data['end_time'] ?: ''
            ])->save();
            if ($this->id) return $this->id;
            return false;
        }
    }


}