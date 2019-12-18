<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-11
 * Time: 21:28
 */

namespace app\common\model;
class Formid extends Base
{
    protected $table = 'vhake_formid';

    public function createFormId($uid, $formid)
    {
        if (!$uid || !$formid || $formid == 'the formId is a mock one') return false;
        $this->allowField(true)->isUpdate(false)->data([
            'uid' => $uid,
            'formid' => $formid
        ])->save();
        if ($this->id) return $this->id;
        return false;
    }

    public function getFormIdByUserId($uid)
    {
        $data = $this->where([
            'flag' => 1,
            'uid' => $uid,
            'create_time' => ['>', time() - (3600 * 24 * 7)]
        ])->field('id,formid,flag')->find();
        if (!$data) return false;
        return $data;
    }
}