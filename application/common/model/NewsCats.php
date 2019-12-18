<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-07
 * Time: 22:51
 */

namespace app\common\model;
class NewsCats extends Base
{
    protected $table = 'vhake_news_cats';

    public function createData($data)
    {
        if ($data['id']) {
            $rows = $this->where([
                'flag' => 1,
                'id' => $data['id']
            ])->update([
                'title' => $data['title'] ?: '',
                'sort' => $data['sort'] ?: 0
            ]);
            if ($rows === false) return false;
            return true;
        } else {
            $this->allowField(true)->isUpdate(false)->data([
                'title' => $data['title'] ?: '',
                'sort' => $data['sort'] ?: 0
            ])->save();
            if ($this->id) return $this->id;
            return false;
        }
    }
}