<?php
/**
 * Created by PhpStorm.
 * User: qiqi-macmini
 * Date: 2018/1/14
 * Time: 下午5:23
 */

namespace app\api\home\v1;

use app\api\home\Base;

use think\exception\HttpResponseException;//集成异常处理类

/**
 * Class Auth 权限父类
 * @package app\api\home\v1
 */
class Auth extends Base
{
    protected $filter_actions_list = [
        'bindUserInfo',
        'getUserInfo',
        'submit'
    ];

    public function __construct()
    {
        parent::__construct();
        if (!$this->user_info) throw new HttpResponseException($this->jsonOth(APIERROR_AUTH, '登录超时'));
        $this->checkIsReg();
    }

    protected function checkIsReg()
    {
        if (!$this->user_info['info_bind'] && !in_array($this->post_json['a'], $this->filter_actions_list)) throw new HttpResponseException($this->jsonOth(BINDUSERINFO, '请授权登录'));
    }

}