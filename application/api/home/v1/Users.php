<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-11
 * Time: 16:06
 */

namespace app\api\home\v1;

use app\common\model\NewsComment as NewsCommentModel;
use app\common\model\News as NewsModel;
use app\common\model\UsersClickLikeComment as UsersClickLikeCommentModel;
use app\common\model\UsersClickLikeNews as UsersClickLikeNewsModel;
use app\common\model\ExchangeGoods as ExchangeGoodsModel;
use app\common\model\UsersExchangeGoodsCheckIn as UsersExchangeGoodsCheckInModel;
use app\common\model\Formid as FormidModel;
use app\common\model\Locals as LocalsModel;
use app\common\model\SingleItem as SingleItemModel;
use app\common\model\UsersLookNews as UsersLookNewsModel;
use think\Cache;
use think\Db;

class Users extends Auth
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * get user info
     */
    public function getUserInfo()
    {
        $this->user_info['level_name'] = $this->getUserLevelName($this->user_info['id']);
        return $this->jsonOk('操作成功', [
            'user_info' => $this->user_info
        ]);
    }

    /**
     * bind user info
     */
    public function bindUserInfo()
    {
        $user_info = $this->post_json['user_info'];
        if (!$user_info) return $this->jsonErr('请先同意微信授权');
        $this->user_info['nickname'] = $user_info['nickName'] ?: '';
        $this->user_info['avatar'] = $user_info['avatarUrl'] ?: '';
        $this->user_info['gender'] = $user_info['gender'] ?: 0;
        $this->user_info['info_bind'] = 1;
        $this->user_info->save();
        return $this->jsonOk('登录成功');
    }

    /**
     * bind mobile
     */
    public function bindMobile()
    {
        $mobile = $this->post_json['mobile'];
        $code = $this->post_json['code'];
        if (!$mobile) return $this->jsonErr('请输入手机号');
        if (!preg_match('/^0?(13|14|15|17|18|19)[0-9]{9}$/', $mobile)) return $this->jsonErr('请输入正确手机号');
        if (!$code) return $this->jsonErr('请输入验证码');
        $out_code = Cache::get('VK-' . $mobile);
        if ($out_code != $code) return $this->jsonErr('验证码错误');
        $users = $this->user_model->findUserByMobile($mobile);
        if ($users && $users['id'] != $this->user_info['id']) return $this->jsonErr('该手机号已绑定用户');
        $this->user_info['mobile'] = $mobile;
        $this->user_info->save();
        return $this->jsonOk('绑定成功');
    }

    /**
     * check is bind mobile
     */
    public function checkIsBindMobile()
    {
        if (!$this->user_info['mobile']) return $this->jsonErr('请先绑定手机号');
        return $this->jsonOk();
    }

    /**
     * 回复评论
     */
    public function postComment()
    {
        Db::startTrans();
        $pid = $this->post_json['pid'] ?: 0;
        $news_id = $this->post_json['news_id'] ?: 0;
        $content = $this->post_json['content'] ?: '';
        if (!$news_id) return $this->jsonErr();
        if (!$content) return $this->jsonErr('请输入评论内容');
        $news_model = new NewsModel();
        $news_data = $news_model->getInfo($news_id, [
            'flag' => 1,
            'status' => 1
        ], 'id,comment_num,title');
        if (!$news_data) return $this->jsonErr('该资讯已删除或不存在');

        $model = new NewsCommentModel();
        if ($pid) {
            $top_comment = $model->getInfo($pid, [
                'flag' => 1,
                'status' => 1,
                'news_id' => $news_data['id']
            ], 'id,comment_num,uid');
            if (!$top_comment) return $this->jsonErr('该评论已删除或不存在');
            $top_comment->comment_num = $top_comment['comment_num'] + 1;
            $top_comment->save();
        }

        $res = $model->createData([
            'news_id' => $news_id,
            'uid' => $this->user_info['id'],
            'u_avatar' => $this->user_info['avatar'],
            'u_nickname' => $this->user_info['nickname'],
            'pid' => $pid,
            'content' => $content
        ]);
        if (!$res) {
            Db::rollback();
            return $this->jsonErr('评论失败');
        }
        // 资讯评论数加1
        $news_data->comment_num = $news_data['comment_num'] + 1;
        $news_data->save();

        if ($pid) {
            // 发送模板消息通知
            $formid = (new FormidModel())->getFormIdByUserId($top_comment['uid']);
            if ($formid) {
                $user = $this->user_model->getInfo($top_comment['uid']);
                if ($user) {
                    $res = (new Wx())->sendTemplateMessage($user['small_openid'], config('template_id')['comment_reply_notification'], $formid['formid'], [
                        'keyword1' => [
                            'value' => $news_data['title']
                        ],
                        'keyword2' => [
                            'value' => date('Y-m-d H:i:s')
                        ],
                        'keyword3' => [
                            'value' => '用户' . $this->user_info['nickname'] . '回复了你的评论。'
                        ],
                        'keyword4' => [
                            'value' => '详情请点击进去小程序查看'
                        ]
                    ], 'pages/index/commentDetail?pid=' . $pid . '&news_id=' . $news_data['id']);
                    if ($res['errcode'] != 0) {
                        Db::rollback();
                        return $this->jsonErr($res['errmsg']);
                    }
                    $formid->flag = -1;
                    $formid->save();
                }
            }
        }

        // 赠送积分
        $single_item = (new SingleItemModel())->getInfo(4, [

        ], 'id,item_value');
        $score = $single_item ? $single_item['item_value'] : 0;
        $this->user_info->score = $this->user_info['score'] + $score;
        $this->user_info->origin_score = $this->user_info['origin_score'] + $score;
        $this->user_info->comment_score = $this->user_info['comment_score'] + $score;
        $this->user_info->comment_num = $this->user_info['comment_num'] + 1;
        $this->user_info->save();

        Db::commit();
        return $this->jsonOk('评论成功', [
            'score' => $score
        ]);
    }

    /**
     * 点赞评论
     */
    public function clickLikeComment()
    {
        Db::startTrans();
        $comment_id = $this->post_json['comment_id'];
        if (!$comment_id) return $this->jsonErr();
        $data = (new NewsCommentModel())->getInfo($comment_id, [
            'flag' => 1,
            'status' => 1
        ], 'id,like_num');
        if (!$data) return $this->jsonErr('该评论已删除或不存在');
        $model = new UsersClickLikeCommentModel();
        $res = $model->clickLike($this->user_info['id'], $comment_id);
        if ($res === false) return $this->jsonErr('操作失败');
        if ($res) {
            $data->like_num = $data['like_num'] + 1;
        } else {
            $data->like_num = ($data['like_num'] - 1) > 0 ? $data['like_num'] - 1 : 0;
        }
        $data->save();
        Db::commit();
        return $this->jsonOk($res ? '点赞成功' : '取消点赞成功', [
            'flag' => $res
        ]);
    }

    /**
     * 点赞资讯
     */
    public function clickLikeNews()
    {
        Db::startTrans();
        $news_id = $this->post_json['news_id'];
        if (!$news_id) return $this->jsonErr();
        $data = (new NewsModel())->getInfo($news_id, [
            'flag' => 1,
            'status' => 1
        ], 'id,like_num');
        if (!$data) return $this->jsonErr('该评论已删除或不存在');
        $model = new UsersClickLikeNewsModel();
        $res = $model->clickLike($this->user_info['id'], $news_id);
        if ($res === false) return $this->jsonErr('操作失败');
        if ($res) {
            $data->like_num = $data['like_num'] + 1;
        } else {
            $data->like_num = ($data['like_num'] - 1) > 0 ? $data['like_num'] - 1 : 0;
        }
        $data->save();
        Db::commit();
        return $this->jsonOk($res ? '点赞成功' : '取消点赞成功', [
            'flag' => $res
        ]);
    }

    /**
     * 检查积分兑换奖品的数量
     */
    public function checkExchangeGoodsNum()
    {
        if (!$this->user_info['mobile']) {
            return $this->jsonOth(444, '请先绑定手机号');
        }
        $id = $this->post_json['id'];
        if (!$id) return $this->jsonErr();
        $data = (new ExchangeGoodsModel())->getInfo($id, [
            'flag' => 1,
            'status' => 1
        ], 'id,num,end_time');
        if (!$data) return $this->jsonErr('该兑换奖品已删除或不存在');
        if ($data['num'] <= 0) return $this->jsonErr('该兑换奖品已全部兑换完毕');
        if (strtotime($data['end_time']) < time()) return $this->jsonErr('该兑换奖品已过有效期');
        return $this->jsonOk('操作成功', [
            'info' => $data
        ]);
    }

    /**
     * 用户兑换奖品
     */
    public function userExchangeGoodsCheckIn()
    {
        Db::startTrans();
        $model = new UsersExchangeGoodsCheckInModel();
        $mobile = $this->post_json['mobile'];
        $id = $this->post_json['id'];
        if (!$id) return $this->jsonErr();
        if (!$mobile) return $this->jsonErr('请输入手机号');
        if (!preg_match('/^0?(13|14|15|17|18|19)[0-9]{9}$/', $mobile)) return $this->jsonErr('请输入正确手机号');

        $goods = (new ExchangeGoodsModel())->getInfo($id, [
            'flag' => 1,
            'status' => 1
        ]);
        if (!$goods) return $this->jsonErr('该兑换奖品已删除或不存在');
        if ($goods['num'] <= 0) return $this->jsonErr('该兑换奖品已全部兑换完毕');
        if (strtotime($goods['end_time']) < time()) return $this->jsonErr('该兑换奖品已过有效期');
        if ($this->user_info['score'] < $goods['score']) return $this->jsonErr('您的积分不足');
        // 生成唯一码
        $unique_code = session_create_id(); // php 7 新增函数
        $data = [
            'uid' => $this->user_info['id'],
            'unique_code' => $unique_code,
            'ticket' => $goods['id'],
            'check_in_mobile' => $mobile,
            'end_time' => $goods['end_time'],
            'exchange_goods_data' => json_encode($goods, true) ?: ''
        ];
        $res = $model->checkIn($data);
        if ($res !== true) {
            Db::rollback();
            return $this->jsonErr($res);
        }
        // 兑换奖品数量减1
        $goods->num = $goods['num'] - 1;
        $goods->save();
        // 用户积分扣除
        $this->user_info->score = $this->user_info['score'] - $goods['score'];
        $this->user_info->save();
        // 模板消息通知
        $formid = (new FormidModel())->getFormIdByUserId($this->user_info['id']);
        if ($formid) {
            $res = (new Wx())->sendTemplateMessage($this->user_info['small_openid'], config('template_id')['successful_redemption_of_points'], $formid['formid'], [
                'keyword1' => [
                    'value' => $this->user_info['nickname']
                ],
                'keyword2' => [
                    'value' => $this->user_info['origin_score']
                ],
                'keyword3' => [
                    'value' => $this->user_info['score'] + $goods['score']
                ],
                'keyword4' => [
                    'value' => $goods['score']
                ],
                'keyword5' => [
                    'value' => $this->user_info['score']
                ],
                'keyword6' => [
                    'value' => $goods['title']
                ],
                'keyword7' => [
                    'value' => date('Y-m-d H:i:s')
                ],
                'keyword8' => [
                    'value' => (new LocalsModel())->field('id,title')->find($goods['locals_id'])['title'] ?: ''
                ],
                'keyword9' => [
                    'value' => '有效期为：' . $goods['end_time']
                ],
                'keyword10' => [
                    'value' => '详情请点击进去小程序【我的】->【我的兑换】中查看'
                ]
            ], 'pages/index/index');
            if ($res['errcode'] === 0) {
                $formid->flag = -1;
                $formid->save();
            } else {
                Db::rollback();
                return $this->jsonErr($res['errmsg']);
            }
        }
        Db::commit();
        return $this->jsonOk('兑换成功');
    }

    /**
     * 获取我的兑换
     */
    public function getMyExchange()
    {
        $page = $this->post_json['page'] ?: 1;
        $list_rows = $this->post_json['list_rows'] ?: 10;
        $data = (new UsersExchangeGoodsCheckInModel())->getList([
            'flag' => 1,
            'uid' => $this->user_info['id']
        ], 'id desc', 'id,end_time,exchange_goods_data,status', $page, $list_rows);
        $data = json_decode(json_encode($data, true), true);
        $status = [
            '1' => '已勾销',
            '0' => '未使用',
            '-1' => '已过期',
            '-2' => '失效'
        ];
        foreach ($data as &$item) {
            $item['exchange_goods_data'] = json_decode($item['exchange_goods_data'], true);
            getPic($item['exchange_goods_data'], 'pic');
            $item['status'] = $status[$item['status']];
        }
        return $this->jsonOk('操作成功', [
            'list' => $data
        ]);
    }

    /**
     * 根据兑换id查询
     */
    public function findExchangeStatus()
    {
        $id = $this->post_json['id'];
        if (!$id) return $this->jsonErr();
        $data = (new UsersExchangeGoodsCheckInModel())->getInfo($id, [
            'flag' => 1,
            'uid' => $this->user_info['id']
        ]);
        if (!$data) return $this->jsonErr('该兑换已删除或不存在');
        if ($data['status'] == 1) return $this->jsonOth(2, '您已兑换', [
            'id' => $data['ticket']
        ]);
        if ($data['status'] == 0) {
            return $this->jsonOk('操作成功', [
                'url' => 'https://liuyanban.ssslover.com/api/v1.index/qrcode?unique_code=' . $data['unique_code']
            ]);
        }
        if ($data['status'] == -1) return $this->jsonOth(2, '已过期', [
            'id' => $data['ticket']
        ]);
        if ($data['status'] == -2) return $this->jsonOth(2, '已失效', [
            'id' => $data['ticket']
        ]);
    }

    /**
     * 勾销
     */
    public function writerOff()
    {
        Db::startTrans();
        $unique_code = $this->post_json['unique_code'];
        if (!$unique_code) return $this->jsonErr();
        $data = (new UsersExchangeGoodsCheckInModel())->findUniqueCode($unique_code);
        if (!$data) return $this->jsonErr('二维码错误');
        if ($data['status'] != 0) {
            switch ($data['status']) {
                case 1:
                    return $this->jsonErr('该兑换信息已勾销');
                    break;
                case -1:
                    return $this->jsonErr('该兑换信息已过期');
                    break;
                case -2:
                    return $this->jsonErr('该兑换信息已失效');
                    break;
                default:
                    return $this->jsonErr('兑换信息有误');
                    break;
            }
        }
        // 检查用户权限
        if ($this->user_info['writer_off_role_type'] == 1) {

        } else if ($this->user_info['writer_off_role_type'] == 2) {
            $writer_off_locals_ids = json_decode($this->user_info['writer_off_locals_ids'], true);
            if (!in_array($data['exchange_goods_data']['locals_id'], $writer_off_locals_ids)) return $this->jsonErr('您没有勾销权限');
        } else {
            return $this->jsonErr('您没有勾销权限');
        }
        $data->status = 1;
        $data->writer_off_user_id = $this->user_info['id'];
        $data->writer_off_time = time();
        $data->save();
        // TODO 预留个接入模板消息提醒的口子
        // 模板消息通知
        $formid = (new FormidModel())->getFormIdByUserId($data['uid']);
        if ($formid) {
            $user = $this->user_model->getInfo($data['uid']);
            if ($user) {
                $res = (new Wx())->sendTemplateMessage($user['small_openid'], config('template_id')['item_status_reminder'], $formid['formid'], [
                    'keyword1' => [
                        'value' => $data['exchange_goods_data']['title']
                    ],
                    'keyword2' => [
                        'value' => '已使用'
                    ],
                    'keyword3' => [
                        'value' => $data['exchange_goods_data']['title'] . '使用成功。'
                    ],
                    'keyword4' => [
                        'value' => date('Y-m-d H:i:s')
                    ],
                    'keyword5' => [
                        'value' => ''
                    ]
                ], 'pages/index/index');
                if ($res['errcode'] != 0) {
                    Db::rollback();
                    return $this->jsonErr($res['errmsg']);
                }
                $formid->flag = -1;
                $formid->save();
            }
        }
        Db::commit();
        return $this->jsonOk('勾销成功');
    }

    public function getMyCommentList()
    {
        $page = $this->post_json['page'] ?: 1;
        $list_rows = $this->post_json['list_rows'] ?: 10;
        $offset = ($page - 1) * $list_rows;
        $data = Db::view('vhake_news_comment', 'id,news_id,uid,u_avatar,u_nickname,pid,content,comment_num,like_num,create_time')
            ->view('vhake_news', 'title,cats_id,pic,video_url,duration,click_num', 'vhake_news_comment.news_id = vhake_news.id', 'LEFT')
            ->where([
                'vhake_news_comment.flag' => 1,
                'vhake_news.flag' => 1,
                'vhake_news.status' => 1,
                'vhake_news_comment.status' => 1,
                'vhake_news_comment.uid' => $this->user_info['id']
            ])
            ->order('vhake_news_comment.id desc')
            ->limit($offset, $list_rows)
            ->select();
        getPic($data, 'pic');
        $level_name = $this->getUserLevelName($this->user_info['id']);
        foreach ($data as &$item) {
            $item['create_time'] = date('m月d日', $item['create_time']);
            $item['level_name'] = $level_name;
        }
        return $this->jsonOk('操作成功', [
            'list' => $data
        ]);
    }

    public function sharePoster()
    {
        $id = $this->post_json['id'] ?: 2;
        if (!$id) return $this->jsonErr();
        $data = (new NewsModel())->getInfo($id);
        if (!$data) return $this->jsonErr('该资讯已删除或不存在');
        $scene = "uid={$this->user_info['id']};id={$data['id']};type_desc=news1";
        $qrcode_bin = (new Wx())->getWXACodeUnlimit($scene, 'pages/index/detail');
        mb_convert_encoding($qrcode_bin, 'UTF-8', 'UTF-8');
        if ($json = json_decode($qrcode_bin, true)) return $this->jsonErr($json['errmsg']);
        file_put_contents('qrcode/qrcode'.$this->user_info['id'].'.png', $qrcode_bin, true);
        return $this->jsonOk('操作成功', [
            'url' => config('domain') . '/qrcode/qrcode'.$this->user_info['id'].'.png'
        ]);
    }

    /**
     * 分享本地生活
     */
    public function shareLocalsPoster()
    {
        $id = $this->post_json['id'];
        if (!$id) return $this->jsonErr();
        $data = (new LocalsModel())->getInfo($id);
        if (!$data) return $this->jsonErr('该商家已删除或不存在');
        $scene = "uid={$this->user_info['id']};id={$data['id']};type_desc=locals1";
        $qrcode_bin = (new Wx())->getWXACodeUnlimit($scene, 'pages/local/detail');
        mb_convert_encoding($qrcode_bin, 'UTF-8', 'UTF-8');
        if ($json = json_decode($qrcode_bin, true)) return $this->jsonErr($json['errmsg']);
        file_put_contents('qrcode/qrcode'.$this->user_info['id'].'.png', $qrcode_bin, true);
        return $this->jsonOk('操作成功', [
            'url' => config('domain') . '/qrcode/qrcode'.$this->user_info['id'].'.png'
        ]);
    }

    /**
     * 生涯
     */
    public function career()
    {
        // 获取信息
        $create_time = date('Y年m月d日', $this->user_info['create_time']); // 注册日期
        $score = $this->user_info['score']; // 当前积分
        $origin_score = $this->user_info['origin_score']; // 累计积分
        $level_name = $this->getUserLevelName($this->user_info['id']);
        $level_name = $level_name ? $level_name['level_name'] : ''; // 称谓
        $comment_score = $this->user_info['comment_score']; // 评论获取的积分
        $read_num = (new UsersLookNewsModel())->getList([
            'flag' => 1,
            'uid' => $this->user_info['id']
        ], 'id desc', null, null, null, false, true); // 阅读量
        $share_score = $this->user_info['share_score']; // 分享累计积分
        $share_wechat_score = $this->user_info['share_wechat_score']; // 分享微信累计积分
        $share_poster_score = $this->user_info['share_poster_score']; // 分享海报积分
        $from_wechat_num = $this->user_info['from_wechat_num']; // 从微信拉来多少人
        $from_poster_num = $this->user_info['from_poster_num']; // 从海报拉来多少人
        $submiss_num = $this->user_info['submiss_num']; // 投稿数
        $adoption_num = $this->user_info['adoption_num']; // 采纳数
        $adoption_score = $this->user_info['adoption_score']; // 采纳累计积分
        $comment_num = $this->user_info['comment_num']; // 评论数
        $exchange_num = (new UsersExchangeGoodsCheckInModel())->getList([
            'uid' => $this->user_info['id']
        ], 'id desc', 'id', null, null, false, true); // 累计兑换奖品数量
        return $this->jsonOk('操作成功', [
            'avatar' => $this->user_info['avatar'],
            'nickname' => $this->user_info['nickname'],
            'create_time' => $create_time,
            'score' => $score,
            'origin_score' => $origin_score,
            'level_name' => $level_name,
            'comment_score' => $comment_score,
            'comment_num' => $comment_num,
            'read_num' => $read_num,
            'share_score' => $share_score,
            'share_wechat_score' => $share_wechat_score,
            'share_poster_score' => $share_poster_score,
            'from_wechat_num' => $from_wechat_num,
            'from_poster_num' => $from_poster_num,
            'submiss_num' => $submiss_num,
            'adoption_num' => $adoption_num,
            'adoption_score' => $adoption_score,
            'exchange_num' => $exchange_num
        ]);
    }
}