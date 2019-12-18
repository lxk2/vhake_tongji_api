<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-04-03
 * Time: 17:09
 */

namespace app\api\admin;
use app\common\model\Users as UsersModel;
use app\common\model\UsersShareLog as UsersShareLogModel;
use app\common\model\News as NewsModel;
use think\Db;
use think\Request;

class Statistics extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    // 页面
    public function index()
    {
        return $this->fetch();
    }

    public function getUsersStatistics()
    {
        $date = input('date');
        $where = '';
        $where_sql = <<<ABC
    flag = 1
ABC;

        if ($date) {
            $arr = explode(' - ', $date);
            $begin = strtotime($arr[0]);
            $end = strtotime($arr[1]);
            if ($begin && $end) {
                $where = <<<ABC
    vhake_users.create_time between {$begin} and {$end} or vhake_users_share_log.create_time between {$begin} and {$end}
ABC;

                $where_sql .= <<<ABC
    and create_time between {$begin} and {$end}
ABC;

            }
        }

        $total_users = (new UsersModel())->getList($where_sql, 'id asc', 'id,from_wechat_num,from_poster_num', null, null, false, true);
        $data = Db::view('vhake_users_share_log')
            ->view('vhake_users', 'nickname', 'vhake_users_share_log.uid = vhake_users.id', 'LEFT')
            ->where([
                'vhake_users_share_log.flag' => 1,
                'vhake_users.flag' => 1
            ])
            ->where($where)
            ->select();
        $poster_num = 0;
        $wechat_num = 0;
        foreach ($data as $item) {
            if ($item['share_type'] == 1) {
                $poster_num++;
            } else if ($item['share_type'] == 2) {
                $wechat_num++;
            }
        }
        return json([
            'code' => 1,
            'data' => [
                'main' => [
                    $total_users, $total_users - $poster_num - $wechat_num,$poster_num, $wechat_num
                ],
                'aid' => [
                    0, $poster_num + $wechat_num, $wechat_num, 0
                ]
            ]
        ]);
    }

    public function newsTotal()
    {
        return $this->fetch('newstotal');
    }

    public function getNewsTotalData()
    {
        $news = (new NewsModel())->getList([
            'flag' => 1
        ], 'is_top desc,sort desc,id asc', 'id,click_num,look_num,like_num,share_circle_friends_num,share_wechat_friends_num,comment_num');
        $title_arr = [];
        $click_num = [];
        $look_num = [];
        $like_num = [];
        $comment_num = [];
        $share_circle_friends_num = [];
        $share_wechat_friends_num = [];
        foreach ($news as $item) {
            array_push($title_arr, 'id:'.$item['id']);
            array_push($click_num, $item['click_num'] ?: 0);
            array_push($look_num, $item['look_num'] ?: 0);
            array_push($like_num, $item['like_num'] ?: 0);
            array_push($comment_num, $item['comment_num'] ?: 0);
            array_push($share_circle_friends_num, $item['share_circle_friends_num'] ?: 0);
            array_push($share_wechat_friends_num, $item['share_wechat_friends_num'] ?: 0);
        }
        return json([
            'code' => 1,
            'data' => [
                'legend_data' => [
                    '点击量', '播放量', '点赞量', '评论数', '海报分享', '好友分享'
                ],
                'xAxis_data' => $title_arr,
                'series_data' => [
                    $click_num, $look_num, $like_num, $comment_num, $share_circle_friends_num, $share_wechat_friends_num
                ]
            ]
        ]);
    }
}