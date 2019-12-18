<?php
/**
 * Created by PhpStorm.
 * User: qiqi-macmini
 * Date: 2018/1/14
 * Time: 下午5:23
 */

namespace app\api\home\v1;

use app\api\home\Base;
use think\Cache;
use app\common\model\SingleItem as SingleItemModel;
use app\common\model\News as NewsModel;
use app\common\model\UsersShareLog as UsersShareLogModel;
use app\common\model\Locals as LocalsModel;

class Publics extends Base
{
    public function login()
    {
        $uid = $this->post_json['uid'];
        $type_desc = $this->post_json['type_desc'];
        $id = $this->post_json['id'];
        $scene = $this->post_json['scene'];

        $code = $this->post_json['code'];
        $openid_obj = $this->getOpenId($code);
        $openid = $openid_obj['openid'];
        if (!$openid) return $this->jsonErr('openid获取失败');
        $token = $this->user_model->smallLogin($openid);
        //查询账号是否存在

        /**
         * 以下为分享积分处理逻辑
         */

        if ($token['code'] == 2) {
            // 分积分
            if ($uid && $type_desc) {
                $user = $this->user_model->getInfo($uid);
                if ($user) {
                    $single_item = (new SingleItemModel())->getInfo(3, [
                        'flag' => 1
                    ], 'item_value');
                    $score = $single_item ? $single_item['item_value'] : 0;
                    $user->score = $user['score'] + $score;
                    $user->origin_score = $user['origin_score'] + $score;
                    $user->share_wechat_score = $user['share_wechat_score'] + $score;
                    $user->from_wechat_num = $user['from_wechat_num'] + 1;
                    $user->save();
                    if ($type_desc == '"news"' && $id) {
                        $res = (new NewsModel())->shareWechatFriendsNum($id);
                        if ($res) {
                            $res = (new UsersShareLogModel())->createData([
                                'uid' => $user['id'],
                                'share_type' => 2,
                                'news_id' => $id,
                                'local_id' => 0,
                                'type_desc' => $type_desc,
                                'score' => $score
                            ]);
                        }
                    }
                    if ($type_desc == '"local_detail"' && $id) {
                        $res = (new LocalsModel())->shareWechatNum($id);
                        if ($res) {
                            $res = (new UsersShareLogModel())->createData([
                                'uid' => $user['id'],
                                'share_type' => 2,
                                'news_id' => 0,
                                'local_id' => $id,
                                'type_desc' => $type_desc,
                                'score' => $score
                            ]);
                        }
                    }
                }
            } else if ($scene) {
                $arr = explode(';', $scene);
                $temp_arr = [];
                foreach ($arr as $item) {
                    $temp = explode('=', $item);
                    $temp_arr[$temp[0]] = $temp[1];
                }
                if ($temp_arr['uid'] && $temp_arr['type_desc']) {
                    $user = $this->user_model->getInfo($uid);
                    if ($user) {
                        $single_item = (new SingleItemModel())->getInfo(3, [
                            'flag' => 1
                        ], 'item_value');
                        $score = $single_item ? $single_item['item_value'] : 0;
                        $user->score = $user['score'] + $score;
                        $user->origin_score = $user['origin_score'] + $score;
                        $user->share_poster_score = $user['share_poster_score'] + $score;
                        $user->from_poster_num = $user['from_poster_num'] + 1;
                        $user->save();
                        if ($temp_arr['type_desc'] == 'news1') {
                            if ($temp_arr['id']) {
                                $res = (new NewsModel())->shareCircleFriendsNum($temp_arr['id']);
                                if ($res) {
                                    $res = (new UsersShareLogModel())->createData([
                                        'uid' => $user['id'],
                                        'share_type' => 1,
                                        'news_id' => $temp_arr['id'],
                                        'local_id' => 0,
                                        'type_desc' => $temp_arr['type_desc'],
                                        'score' => $score
                                    ]);
                                }
                            }
                        } else if ($temp_arr['type_desc'] == 'career_poster') {
                            $res = (new UsersShareLogModel())->createData([
                                'uid' => $user['id'],
                                'share_type' => 1,
                                'news_id' => 0,
                                'local_id' => 0,
                                'type_desc' => $temp_arr['type_desc'],
                                'score' => $score
                            ]);
                        } else if ($temp_arr['type_desc'] == 'locals1') {
                            if ($temp_arr['id']) {
                                $res = (new LocalsModel())->sharePosterNum($temp_arr['id']);
                                if ($res) {
                                    $res = (new UsersShareLogModel())->createData([
                                       'uid' => $user['id'],
                                       'share_type' => 1,
                                       'news_id' => 0,
                                       'local_id' => $temp_arr['id'],
                                       'type_desc' => $temp_arr['type_desc'],
                                       'score' => $score
                                    ]);
                                }
                            }
                        }
                    }

                }
            }
        }

        return $this->jsonOk('登录成功', $token['data']);
    }

    public function getCode()
    {
        $mobile = $this->post_json['mobile'];
        if (!$mobile) return $this->jsonErr('请输入手机号');
        if (!preg_match('/^0?(13|14|15|17|18|19)[0-9]{9}$/', $mobile)) return $this->jsonErr('请输入正确手机号');
        $res = httpRequest(config('msg_url'), 'POST', json_encode([
            'mobile' => $mobile
        ], true));
        $res = json_decode($res, true);
        if ($res['code'] == 1) {
            Cache::set('VK-' . $mobile, $res['data']['code'], 5 * 60);
            return $this->jsonOk('发送成功');
        }
        return $this->jsonErr($res['msg']);
    }
}