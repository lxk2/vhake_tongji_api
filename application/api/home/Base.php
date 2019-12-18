<?php

namespace app\api\home;

use think\Controller;
use app\common\model\Users as UserModel;
use app\common\model\LevelSet as LevelSetModel;

// 导入外围库
\think\Loader::addNamespace('data', '../vhake/');

class Base extends Controller
{
    public $post_json, $user_info, $user_model;

    public function __construct()
    {
        parent::__construct();
        $this->user_model = new UserModel();
        $this->post_json = json_decode(file_get_contents('php://input'), true);
        $this->isLogin();
    }

    public function isLogin()
    {
        $token = $this->post_json['token'];
        if (!$token) {
            return false;
        }
        $session_info = cache($token);
        if ($session_info) {
            $user_info = $this->user_model->getInfo($session_info);
            if ($user_info) {
                $this->user_info = $user_info;
                return $user_info;
            } else return false;
        } else return false;
    }

    /**
     * 找出用户的称谓
     */
    protected function getUserLevelName($uid)
    {
        $model = new LevelSetModel();
        $user = $this->user_model->getInfo($uid, ['flag' => 1], 'id,origin_score');
        if (!$user) return false;
        $data = $model->getLevelName($user['origin_score']);
        if ($data) return $data;
        return false;
    }

    /**
     * 入口
     */
    public function Index()
    {
        $a = $this->post_json['a'];
        return $this->$a();
    }

    /**
     * 返回成功
     * @param string $msg
     * @param null $data
     * @return \think\response\Json
     */
    public function jsonOk($msg = '操作成功', $data = null)
    {
        return json([
            'code' => APISUCCESS,
            'msg' => $msg,
            'data' => $data
        ]);
    }

    /**
     * 返回失败
     * @param string $msg
     * @param null $data
     * @return \think\response\Json
     */
    public function jsonErr($msg = '非法请求', $data = null)
    {
        return json([
            'code' => APIERROR,
            'msg' => $msg,
            'data' => $data
        ]);
    }

    /**
     * 返回其他
     * @param $code
     * @param $msg
     * @param null $data
     * @return \think\response\Json
     */
    public function jsonOth($code, $msg, $data = null)
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ]);
    }

    protected function getOpenid($code)
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