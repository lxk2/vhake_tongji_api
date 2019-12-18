<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 2018/3/5
 * Time: 上午10:18
 */

namespace app\common\model;

class Users extends Base
{
    protected $table = 'vhake_users';

    public function smallLogin($openid)
    {
        $obj = $this->where([
            'small_openid' => $openid,
            'flag' => 1
        ])->find();
        if ($obj) {
            $token = md5($openid . time() . '#$@%!*');
            cache($token, $obj->id);
            return [
                'code' => 1,
                'data' => [
                    'token' => $token
                ]
            ];
        } else {
            //自动注册流程
            $this->data([
                'small_openid' => $openid
            ])->save();
            $token = md5($openid . time() . '#$@%!*');
            cache($token, $this->id);
            return [
                'code' => 2,
                'data' => [
                    'token' => $token
                ]
            ];
        }
    }

    public function findUserByMobile($mobile)
    {
        $data = $this->where([
            'flag' => 1,
            'mobile' => $mobile
        ])->find();
        if ($data) return $data;
        return false;
    }
}