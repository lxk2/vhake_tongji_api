<?php
/**
 * Created by PhpStorm.
 * User: qiqi-book
 * Date: 2019/1/21
 * Time: 6:50 PM
 */
namespace app\common\hook;
use think\exception\HttpResponseException;//集成异常处理类


define('APISUCCESS', 1);
define('APIERROR', 0);
define('APIERROR_AUTH', -1);
define('APIERROR_ORDER_OK_PAY', 10);//下单成功并支付成功
define('APIERROR_USER_AUTH_ING', 102);//用户资质审核中

class HookBase {


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


    public function arrOth($code, $msg, $data = null) {
        return [
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ];
    }

    /**
     * 发送消息回去
     * @param $type 类型H5或者其他
     * @param $code
     * @param $msg
     * @param $data
     */
    protected function callbackMsg($type, $code, $msg, $data = [])
    {
        switch ($type) {
            case 'H5':
                return $this->jsonOth($code, $msg, $data);
            case "Api":
                throw new HttpResponseException($this->jsonOth($code, $msg, $data));
                exit();
            case "Callback":
                return $this->arrOth($code, $msg, $data);
            default:
                throw new HttpResponseException($this->jsonOth($code, $msg, $data));
                exit();
        }
    }
}