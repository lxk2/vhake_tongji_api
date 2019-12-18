<?php

namespace app\api\home;

use think\Controller;

// 导入外围库
\think\Loader::addNamespace('data', '../vhake/');

class Base extends Controller
{
    public $post_json;

    public function __construct()
    {
        parent::__construct();
        $this->post_json = json_decode(file_get_contents('php://input'), true);
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
}