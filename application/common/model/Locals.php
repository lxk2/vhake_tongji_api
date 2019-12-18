<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-12
 * Time: 17:02
 */

namespace app\common\model;
class Locals extends Base
{
    protected $table = 'vhake_locals';

    public function createData($data)
    {
        if ($data['id']) {
            $rows = $this->where([
                'flag' => 1,
                'id' => $data['id']
            ])->update([
                'cats_id' => $data['cats_id'] ?: 0,
                'title' => $data['title'] ?: '',
                'pic' => $data['pic'] ?: '',
                'loop_pic' => $data['loop_pic'] ?: '',
                'mobile' => $data['mobile'] ?: '',
                'map' => $data['map'] ?: '',
                'map_address' => $data['map_address'] ?: '',
                'content' => $data['content'] ?: '',
                'sort' => $data['sort'] ?: 0,
                'small_app_name' => $data['small_app_name'] ?: '',
                'small_appid' => $data['small_appid'] ?: '',
                'small_app_path' => $data['small_app_path'] ?: '',
                'merchant_name' => $data['merchant_name'] ?: '',
                'merchant_time' => $data['merchant_time'] ?: ''
            ]);
            if ($rows === false) return false;
            return true;
        } else {
            $this->allowField(true)->isUpdate(false)->data([
                'cats_id' => $data['cats_id'] ?: 0,
                'title' => $data['title'] ?: '',
                'pic' => $data['pic'] ?: '',
                'loop_pic' => $data['loop_pic'] ?: '',
                'mobile' => $data['mobile'] ?: '',
                'map' => $data['map'] ?: '',
                'map_address' => $data['map_address'] ?: '',
                'content' => $data['content'] ?: '',
                'sort' => $data['sort'] ?: 0,
                'small_app_name' => $data['small_app_name'] ?: '',
                'small_appid' => $data['small_appid'] ?: '',
                'small_app_path' => $data['small_app_path'] ?: '',
                'merchant_name' => $data['merchant_name'] ?: '',
                'merchant_time' => $data['merchant_time'] ?: ''
            ])->save();
            if ($this->id) return $this->id;
            return false;
        }
    }

    /**
     * 增加分享总量和分享微信好友总量
     */
    public function shareWechatNum($id)
    {
        $data = $this->where([
            'flag' => 1
        ])->field('id,share_num,share_wechat_num')->find($id);
        if (!$data) return false;
        $data->share_num = $data['share_num'] + 1;
        $data->share_wechat_num = $data['share_wechat_num'] + 1;
        $data->save();
        return true;
    }

    /**
     * 增加分享总量和分享海报量
     */
    public function sharePosterNum($id)
    {
        $data = $this->where([
            'flag' => 1
        ])->field('id,share_num,share_poster_num')->find($id);
        if (!$data) return false;
        $data->share_num = $data['share_num'] + 1;
        $data->share_poster_num = $data['share_poster_num'] + 1;
        $data->save();
        return true;
    }
}