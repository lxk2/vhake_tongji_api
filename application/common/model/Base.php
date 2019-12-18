<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 2018/1/3
 * Time: 下午4:51
 */

namespace app\common\model;

use think\Model;

class Base extends Model
{
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * @param $where
     * @param string $order
     * @param null $field
     * @param null $page
     * @param null $list_rows
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($where, $order = 'id desc', $field = null, $page = null, $list_rows = null, $is_page = false, $is_count = false)
    {
        $obj = $this;
        if ($field) $obj = $obj->field($field);
        $obj = $obj->where($where)->order($order);
        if ($page && $list_rows) {
            $offset = ($page - 1) * $list_rows;
            $obj = $obj->limit($offset, $list_rows);
        }
        if ($is_page) {
            $data = $obj->paginate();
            return $data;
        }
        if ($is_count) {
            $data = $obj->count();
            return $data;
        }
        return $obj->select();
    }

    /**
     * @param $id
     * @param array $where
     * @param null $field
     * @return array|bool|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInfo($id, $where = ['flag' => 1], $field = null)
    {
        $obj = $this;
        if ($field) $obj = $obj->field($field);
        $data = $obj->where($where)->find($id);
        if ($data) return $data;
        return false;
    }

    /**
     * @param $id
     * @return bool
     */
    public function del($id)
    {
        $rows = $this->where([
            'flag' => 1,
            'id' => $id
        ])
            ->update([
                'flag' => -1
            ]);
        if ($rows) return true;
        return false;
    }

    /**
     * @param array $ids
     * @return bool
     */
    public function delAll(array $ids)
    {
        $rows = $this->where([
            'flag' => 1,
            'id' => ['in', $ids]
        ])->update([
            'flag' => -1
        ]);
        if ($rows === false) return false;
        return true;
    }
}