<?php

/**
 * 会员中心
 */
namespace User\Controller;
use Common\Controller\MemberbaseController;
use Portal\Controller\IndexController;
//MemberbaseController
class CenterController extends IndexController {
	
	protected $users_model;
	function _initialize(){
		parent::_initialize();
		$this->users_model=D("Common/Users");
	}
    //会员中心
	public function index() {
		$userid=sp_get_current_userid();
		$user=$this->users_model->where(array("id"=>$userid))->find();
		$this->assign($user);
		$this->assign("servePromise",$this->_getAd("servePromise"));
    	$this->display(':user_account');
    }
    public function userrule() {
    	$userid=sp_get_current_userid();
    	$user=$this->users_model->where(array("id"=>$userid))->find();
    	$this->assign($user);
    	$this->assign("servePromise",$this->_getAd("servePromise"));
    	$this->display(':userrule');
    }
    public function pointlist() {
    	$userid=sp_get_current_userid();
    	$user=$this->users_model->where(array("id"=>$userid))->find();
    	$this->assign($user);
    	$this->assign("servePromise",$this->_getAd("servePromise"));
    	$this->display(':pointlist');
    }
}
