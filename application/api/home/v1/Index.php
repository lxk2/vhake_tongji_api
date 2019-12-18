<?php
/**
 * Created by PhpStorm.
 * User: qiqi-macmini
 * Date: 2018/1/14
 * Time: 下午5:23
 */

namespace app\api\home\v1;

use app\api\home\Base;
use app\common\model\Formid as FormidModel;
use app\common\model\ShareAppMessage as ShareAppMessageModel;

class Index extends Base
{
    public function createFormId()
    {
        $data = (new FormidModel())->createFormId($this->user_info['id'], $this->post_json['formid']);
        if ($data) return $this->jsonOk();
        return $this->jsonErr();
    }

    /**
     * 获取分享页面设置
     */
    public function getShareAppMessage()
    {
        $id = $this->post_json['id'];
        if (!$id) return $this->jsonErr();
        $data = (new ShareAppMessageModel())->getShareInfo($id);
        if (!$data) return $this->jsonErr('该记录已删除或不存在');
        return $this->jsonOk('操作成功', [
            'info' => $data
        ]);
    }

    public function qrcode($unique_code)
    {
        return plugin_action('Qrcode/Qrcode/generate', [$unique_code]);
    }

    public function getE()
    {
        $e = $this->post_json['e'];
        if ($e) file_put_contents('e.txt', json_encode($e, true));
        return $this->jsonOk();
    }

    /**
     * 生涯分享（）poster
     */
    public function careerPoster()
    {
        $uid = $this->user_info['id'] ?: 0;
        $scene = "uid={$uid};type_desc=career_poster";
        $qrcode_bin = (new Wx())->getWXACodeUnlimit($scene, '');
        mb_convert_encoding($qrcode_bin, 'UTF-8', 'UTF-8');
        if ($json = json_decode($qrcode_bin, true)) return $this->jsonErr($json['errmsg']);
        file_put_contents('qrcode/qrcode'.$this->user_info['id'].'.png', $qrcode_bin, true);
        return $this->jsonOk('操作成功', [
            'url' => config('domain') . '/qrcode/qrcode'.$this->user_info['id'].'.png',
            'avatar' => $this->user_info['avatar']
        ]);
    }
}