<?php
/**
 * 微信红包
 */
namespace Wx\Controller;
use Think\Log;
class HongBaoController extends IndexController {
	
    public $model_user;
	function _initialize() {
		parent::_initialize();
		if(!$this->model_config)
			$this->model_config = D("WxConfig");
		$this->model_user=D("WxUser");
	}
	/**
	 * 双十一领取红包名单
	 */
	public function double11(){
	    $where     = "1=1";
	    $parameter = "";
	    if(!empty($_POST['tel'])){
	        $where .= " and h.tel like '%{$_POST['tel']}%'";
	        $parameter .= "/tel/".$_POST['tel'];
	    }
	    $model = D("HongbaoDouble11");
	    $count = $model->alias("h")->where($where)->count();
	    $page = $this->page($count,20);
	    $list = $model->alias("h")->where($where)->order("add_time desc")->select();
	    $this->assign("list",$list);
	    $this->display();
	}
	
	/**
	 * 双十一红包发放状态
	 */
	public function double11Send(){
	    try {
	        $model = D("HongbaoDouble11");
	        $data = array(
	            "is_send" => $_POST['status'],
	            "send_time"=>""
	        );
	        if($data['is_send']==1) 
	            $data['send_time']=date("Y-m-d H:i:s");
	        
	        $result = $model->where("id={$_POST['id']}")->save($data);
	        if($result){
	            $this->success("操作成功");
	        }else{
	            $this->error("系统繁忙，请稍后再试");
	        }
	    } catch (\Exception $e) {
	        $this->error($e->getMessage());
	    }
	}
}

