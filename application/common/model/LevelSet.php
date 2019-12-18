<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-14
 * Time: 18:05
 */

namespace app\common\model;
class LevelSet extends Base
{
    protected $table = 'vhake_level_set';

    public function createData($data)
    {
        if ($data['id']) {
            $rows = $this->where([
                'flag' => 1,
                'id' => $data['id']
            ])->update([
                'score' => $data['score'] ?: 0,
                'level_name' => $data['level_name'] ?: '',
                'level_icon' => $data['level_icon'] ?: '',
                'level_color' => $data['level_color'] ?: ''
            ]);
            if ($rows === false) return false;
            return true;
        } else {
            $this->allowField(true)->isUpdate(false)->data([
                'score' => $data['score'] ?: 0,
                'level_name' => $data['level_name'] ?: '',
                'level_icon' => $data['level_icon'] ?: '',
                'level_color' => $data['level_color'] ?: ''
            ])->save();
            if ($this->id) return $this->id;
            return false;
        }
    }

    public function getLevelName($score)
    {
        $data = $this->where([
            'score' => ['<=', $score],
            'flag' => 1,
            'status' => 1
        ])->order('score desc,id desc')->field('id,level_name,level_icon,level_color')->find();
        if ($data) {
            $data = json_decode(json_encode($data, true), true);
            getPic($data, 'level_icon');
            return $data;
        }
        return false;
    }
}