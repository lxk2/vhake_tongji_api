<?php
/**
 * Created by PhpStorm.
 * User: qiqi-book
 * Date: 2019/1/21
 * Time: 4:36 PM
 */

namespace app\common\hook;

use app\common\hook\model\SysHook as SysHookModel;
use app\user\model\User;

class Hook
{

    public function __construct()
    {

    }

    /**
     * 实例化挂钩点入口
     */
    public function init()
    {

    }

    /**
     * 运行
     */
    public static function run($tag,$options = null, $rule = null)
    {
        $hook_obj = SysHookModel::where([
            'tag_name' => $tag,
            'flag' => 1
        ])->find();

        if (!$hook_obj) return false;

        if ($hook_obj['status'] != 1) return false;

        // run
        $run_class_name = $hook_obj['class_name'];
        $model = new $run_class_name;


        //func
        $run_func = $hook_obj['func'];
        $obj = $model->$run_func($options);

        return $obj;
    }


}