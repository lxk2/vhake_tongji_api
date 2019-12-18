<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-12
 * Time: 15:04
 */

namespace app\api\home\v1;

use app\api\home\Base;
use think\Cache;

use think\exception\HttpResponseException;//集成异常处理类

class Wx extends Base
{
    /**
     * getAccessToken
     */
    protected function getAccessToken()
    {
        $access_token = Cache::get('small_app_access_token');
        if (!$access_token) {
            $config = config('wechat_small_app');
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$config['appid']}&secret={$config['secret']}";
            $res = httpRequest($url, 'GET');
            $res = json_decode($res, true);
            if ($res['errcode'] != 0) throw new HttpResponseException($this->jsonErr($res['errmsg']));
            $access_token = $res['access_token'];
            $expires_in = $res['expires_in'];
            Cache::set('small_app_access_token', $access_token, $expires_in);
            return $access_token;
        }
        return $access_token;
    }

    /**
     * getWXACodeUnlimit
     */
    public function getWXACodeUnlimit($scene, $page = 'pages/index/index')
    {
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$access_token}";
        $res = httpRequest($url, 'POST', json_encode([
            'scene' => $scene,
            'page' => $page
        ], true));
        return $res;
    }

    /**
     * sendTemplateMessage
     */
    public function sendTemplateMessage($touser, $template_id, $form_id, $data, $page)
    {
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . $access_token;
        $res = httpRequest($url, 'POST', json_encode([
            'touser' => $touser,
            'template_id' => $template_id,
            'page' => $page,
            'form_id' => $form_id,
            'data' => $data
        ]));
        $res = json_decode($res, true);
        return $res;
    }


}