<?php

/**
 * 会员中心
 */
namespace User\Controller;

use Common\Controller\MemberbaseController;
use Portal\Controller\IndexController;

class CenterController extends MemberbaseController {
	
	protected $users_model;
	function _initialize(){
		parent::_initialize();
		$this->users_model=D("Common/Users");
	}
	function __construct(){
		parent::__construct();
		$userid=sp_get_current_userid();
		$user=$this->users_model->where(array("id"=>$userid))->find();
		$this->assign($user);
		$this->assign("servePromise",$this->_getAd("servePromise"));
	}
    //会员中心
	public function index() {
    	$this->display(':user_account');
    }
    public function userrule() {
    	$this->display(':userrule');
    }
    public function pointlist() {
    	$this->display(':pointlist');
    }
}
