<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-07
 * Time: 22:57
 */

namespace app\api\admin;
use app\admin\controller\Admin;
use think\Request;

class Base extends Admin
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }
}