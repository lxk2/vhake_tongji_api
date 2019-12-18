<?php
/**
 * Created by PhpStorm.
 * User: ccb
 * Date: 2019-03-13
 * Time: 14:52
 */

namespace app\api\admin;
use app\common\builder\ZBuilder;
use think\Loader;
use think\Request;
use app\common\model\SingleItem as SingleItemModel;

class SingleItem extends Base
{
    protected $model;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new SingleItemModel();
    }

    public function aboutUs()
    {
        if ($this->request->isPost()) {
            $data = input();
            $validate = Loader::validate('SingleItem');
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
                ['static', 'item_key', '标题', '', $data['item_key'], true],
                ['ueditor', 'item_value', '内容'],
            ])
            ->setFormData($data)
            ->fetch();
    }

    /**
     * 联系我们
     */
    public function contactUs()
    {
        if ($this->request->isPost()) {
            $data = input();
            $validate = Loader::validate('SingleItem');
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
                ['static', 'item_key', '标题', '', $data['item_key'], true],
//                ['text', 'item_key', '标题'],
                ['number', 'item_value', '内容'],
            ])
            ->setFormData($data)
            ->fetch();
    }

    /**
     * 分享赠送积分
     */
    public function shareGiftScore()
    {
        if ($this->request->isPost()) {
            $data = input();
            $validate = Loader::validate('SingleItem');
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
                ['static', 'item_key', '标题', '', $data['item_key'], true],
//                ['text', 'item_key', '标题'],
                ['number', 'item_value', '积分'],
            ])
            ->setFormData($data)
            ->fetch();
    }

    /**
     * 回复评论积分设置
     */
    public function commentScore()
    {
        if ($this->request->isPost()) {
            $data = input();
            $validate = Loader::validate('SingleItem');
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
                ['static', 'item_key', '标题', '', $data['item_key'], true],
//                ['text', 'item_key', '标题'],
                ['number', 'item_value', '积分'],
            ])
            ->setFormData($data)
            ->fetch();
    }
}