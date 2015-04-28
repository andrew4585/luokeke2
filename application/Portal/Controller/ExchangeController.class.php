<?php
namespace Portal\Controller;

use Common\Controller\MemberbaseController;

class ExchangeController extends MemberbaseController
{
    protected $model_exchange;

    function _initialize(){
        parent::_initialize();
        $this->model_exchange=D("Exchange");
    }

    function index()
    {
        if(IS_POST) {
            $user = $_SESSION['user'];
            $id = I('get.id', 0, 'intval');
            if (!$id) $this->error('请选择具体的商品！', __ROOT__ . "/Portal/shop/index");
            $shop_score = D('Shop')->where("id = $id")->find();
            $remain = $shop_score['remain'];
            if($remain<1)  $this->error('余量不足！', __ROOT__ . "/Portal/shop/info/id/$id");
            $shop_score = $shop_score['post_score'];
            $users = D('Users');
            $user_id = $user['id'];
            $user_score = $users->field('score')->where("id = $user_id")->find();
            if ($user_score['score'] < $shop_score) {
                $this->error('您积分不足！', __ROOT__ . "/Portal/shop/info/id/$id");
            }
            $_POST['gid']       = $id;
            $_POST['uid']       = $user['id'];
            $_POST['post_date'] = time();
            $_POST['point']     = $shop_score;
            if (!preg_match("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/", $_POST['phone'])) {
                $this->error('手机号码格式不正确！', __ROOT__ . "/Portal/shop/info/id/$id");
            }
            $sumPoint = $this->model_exchange->where("uid=%d and gid=%d and post_date<%d",array($user['id'],0,$_POST['post_date']))->sum('point');
            $_POST['sumPoint'] = $sumPoint;
            $result = $this->model_exchange->add($_POST);
            if ($result) {
                $data['score'] = ($user_score['score']-$shop_score);
                $rs = $users->where("id = $user_id")->save($data);
                $result	= D('Shop')->where("id=$id")->setDec("remain",1);
                if($result&&$rs) $this->success("提交成功!");
            } else {
                $this->error("提交失败！");
            }
        }else{
            $this->error('页面不存在','/Portal/shop/index');
        }


    }
}