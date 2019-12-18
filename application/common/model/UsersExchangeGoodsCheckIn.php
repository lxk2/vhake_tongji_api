<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-14
 * Time: 12:21
 */

namespace app\common\model;
class UsersExchangeGoodsCheckIn extends Base
{
    protected $table = 'vhake_users_exchange_goods_check_in';

    public function checkIn($data)
    {
        if ($res = $this->checkInValidate($data) !== true) return $res;
        $this->allowField(true)->isUpdate(false)->data($data)->save();
        if ($this->id) return true;
        return '兑换失败';
    }

    protected function checkInValidate($data)
    {
        if (!$data['uid']) return '登录超时';
        if (!$data['unique_code']) return '缺少唯一码';
        if (!$data['ticket']) return '请选择兑换奖品';
        if (!$data['check_in_mobile']) return '请输入手机号';
        if (!preg_match('/^0?(13|14|15|17|18|19)[0-9]{9}$/', $data['check_in_mobile'])) return '请输入正确手机号';
        if (!$data['end_time']) return '缺少有效期';
        return true;
    }

    public function findUniqueCode($unique_code)
    {
        $data = $this->where([
            'flag' => 1,
            'unique_code' => $unique_code
        ])->find();
        if ($data) {
            $data['exchange_goods_data'] = json_decode($data['exchange_goods_data'], true);
            return $data;
        }
        return false;
    }
}