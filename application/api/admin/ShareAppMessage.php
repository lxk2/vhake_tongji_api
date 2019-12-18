<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-12
 * Time: 14:22
 */

namespace app\api\admin;
use app\common\builder\ZBuilder;
use think\Loader;
use think\Request;
use app\common\model\ShareAppMessage as ShareAppMessageModel;

class ShareAppMessage extends Base
{
    protected $model;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ShareAppMessageModel();
    }

    /**
     * 首页分享设置
     */
    public function indexPage()
    {
        if ($this->request->isPost()) {
            $data = input();
            if (!$data['id']) $this->error('数据错误');
            $validate = Loader::validate('ShareAppMessage');
            if (!$validate->check($data)) $this->error($validate->getError());
            $res = $this->model->createData($data);
            if ($res) $this->success('修改成功');
            $this->error('修改失败');
        }
        $data = $this->model->getInfo(1);
        if (!$data) $this->error('该记录已删除或不存在');
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['text', 'title', '分享标题'],
                ['image', 'pic', '分享配图'],
            ])
            ->setFormData($data)
            ->fetch();
    }

    /**
     * 本地生活分享设置
     */
    public function localPage()
    {
        if ($this->request->isPost()) {
            $data = input();
            if (!$data['id']) $this->error('数据错误');
            $validate = Loader::validate('ShareAppMessage');
            if (!$validate->check($data)) $this->error($validate->getError());
            $res = $this->model->createData($data);
            if ($res) $this->success('修改成功');
            $this->error('修改失败');
        }
        $data = $this->model->getInfo(2);
        if (!$data) $this->error('该记录已删除或不存在');
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['text', 'title', '分享标题'],
                ['image', 'pic', '分享配图'],
            ])
            ->setFormData($data)
            ->fetch();
    }

    /**
     * 积分兑换分享设置
     */
    public function exchangeGoodsPage()
    {
        if ($this->request->isPost()) {
            $data = input();
            if (!$data['id']) $this->error('数据错误');
            $validate = Loader::validate('ShareAppMessage');
            if (!$validate->check($data)) $this->error($validate->getError());
            $res = $this->model->createData($data);
            if ($res) $this->success('修改成功');
            $this->error('修改失败');
        }
        $data = $this->model->getInfo(3);
        if (!$data) $this->error('该记录已删除或不存在');
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['text', 'title', '分享标题'],
                ['image', 'pic', '分享配图'],
            ])
            ->setFormData($data)
            ->fetch();
    }

    /**
     * 投稿箱分享设置
     */
    public function manuscriptBoxPage()
    {
        if ($this->request->isPost()) {
            $data = input();
            if (!$data['id']) $this->error('数据错误');
            $validate = Loader::validate('ShareAppMessage');
            if (!$validate->check($data)) $this->error($validate->getError());
            $res = $this->model->createData($data);
            if ($res) $this->success('修改成功');
            $this->error('修改失败');
        }
        $data = $this->model->getInfo(4);
        if (!$data) $this->error('该记录已删除或不存在');
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['text', 'title', '分享标题'],
                ['image', 'pic', '分享配图'],
            ])
            ->setFormData($data)
            ->fetch();
    }

    /**
     * 关于我们分享设置
     */
    public function aboutUsPage()
    {
        if ($this->request->isPost()) {
            $data = input();
            if (!$data['id']) $this->error('数据错误');
            $validate = Loader::validate('ShareAppMessage');
            if (!$validate->check($data)) $this->error($validate->getError());
            $res = $this->model->createData($data);
            if ($res) $this->success('修改成功');
            $this->error('修改失败');
        }
        $data = $this->model->getInfo(5);
        if (!$data) $this->error('该记录已删除或不存在');
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['text', 'title', '分享标题'],
                ['image', 'pic', '分享配图'],
            ])
            ->setFormData($data)
            ->fetch();
    }
}