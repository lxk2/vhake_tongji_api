<?php
namespace data\extend;



class WechatSmallApp {

    public function getOpenId($code)
    {
        $wechat_app_data = config('wechat_small_app');
        $url = 'https://api.weixin.qq.com/sns/jscode2session?' .
            'appid=' . $wechat_app_data['appid'] .
            '&secret=' . $wechat_app_data['secret'] .
            '&js_code=' . $code .
            '&grant_type=authorization_code';
        return json_decode(httpRequest($url, 'GET'), true);
    }
}