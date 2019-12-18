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
    public function __construct()
    {
        parent::__construct();
    }
}