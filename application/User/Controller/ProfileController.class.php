<?php

/**
 * 会员中心
 */
namespace User\Controller;
use Common\Controller\MemberbaseController;
class ProfileController extends MemberbaseController {
	
	protected $users_model;
	function _initialize(){
		parent::_initialize();
		$this->users_model=D("Common/Users");
	}

	function __construct(){
		parent::__construct();
		$this->assign("home_head",$this->_getAd("banner_user"));
	}

    //编辑用户资料
	public function edit() {
		$userid=sp_get_current_userid();
		$user=$this->users_model->where(array("id"=>$userid))->find();
		$this->assign($user);
		$this->assign("servePromise",$this->_getAd("servePromise"));
    	$this->display();
    }
    public function edit_post() {
    	if(IS_POST){
    		$userid					= sp_get_current_userid();   		
    		$_POST['id']			= $userid;
    		if ($this->users_model->create($_POST)) {
				if ($this->users_model->save()!==false) {
					$user=$this->users_model->find($userid);
					sp_update_current_user($user);
					$this->success("保存成功！",U("user/profile/edit"));
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->users_model->getError());
			}
    	}
    	
    }
    
    public function password() {
    	$userid=sp_get_current_userid();
    	$user=$this->users_model->where(array("id"=>$userid))->find();
    	$this->assign($user);
    	$this->assign("servePromise",$this->_getAd("servePromise"));
    	$this->display();
    }
    
    public function password_post() {
    	if (IS_POST) {
    		if(empty($_POST['password'])){
    			$this->error("新密码不能为空！");
    		}
    		$uid=sp_get_current_userid();
    		$admin=$this->users_model->where("id=$uid")->find();
    		$repassword 	= $_POST['repassword'];
    		$password 		= $_POST['password'];
			if($repassword == $password){
				$data['user_pass'] 	= sp_password($password);
				$data['id'] 		= $uid;
				$res = $this->users_model->save($data);
				if($res){
					$this->success("修改成功！");
				}else{
					$this->error("修改失败!");
				}
			}else{
				$this->error("密码输入不一致！");
			}
    	}
    	 
    }
    
    
    function bang(){
    	$oauth_user_model=M("OauthUser");
    	$uid=sp_get_current_userid();
    	$oauths=$oauth_user_model->where(array("uid"=>$uid))->select();
    	$new_oauths=array();
    	foreach ($oauths as $oa){
    		$new_oauths[strtolower($oa['from'])]=$oa;
    	}
    	$this->assign("oauths",$new_oauths);
    	$this->display();
    }
    
    function avatar(){
    	$userid=sp_get_current_userid();
		$this->assign("servePromise",$this->_getAd("servePromise"));
		$user=$this->users_model->where(array("id"=>$userid))->find();
		$this->assign('user',$user);
    	$this->display();
    }

    function avatar_upload(){

		if(IS_POST){
		    $result = array();
		    $result['success'] = false;
			try{
				$userid		= session("user.id");
				list($a,$type) = explode("/", $_FILES['__avatar1']['type']);
				$savename = "$userid.$type";
    			//上传处理类
        		$config=array(
        				'rootPath' => './data/upload/avatar/',
        				'savePath' => '',
        				'maxSize' => 11048576,
        				'saveName'   =>    $savename,
        				'autoSub'    =>    false,
        		);
        		if(file_exists($config['rootPath'].$savename))
        		    unlink($config['rootPath'].$savename);
        		    
        		$upload = new \Think\Upload($config);
        		$info=$upload->uploadOne($_FILES["__avatar1"]);
        		//开始上传
        		if ($info) {
        		    D("Users")->where("id=$userid")->setField("avatar",$savename);
        		} else {
        			//上传失败，返回错误
        			E($upload->getError());
        		}
				$result['success'] = true;
				echo json_encode($result);
			}catch (\Exception $e){
				$result['msg'] = $e->getMessage();
				echo json_encode($result);
			}

		}
    }
    function usercontact(){
    	$userid=sp_get_current_userid();
    	$user=$this->users_model->where(array("id"=>$userid))->find();
    	$this->assign("user",$user);
    	$this->assign("servePromise",$this->_getAd("servePromise"));
    	$this->display();
    }

	function usercontact_post(){
		if(IS_POST){
			$userid = sp_get_current_userid();
			$_POST['id'] = $userid;
			if($this->users_model->create($_POST)){
				if($this->users_model->save()){
					$this->success("保存成功！",U("user/profile/usercontact"));
				}else{
					$this->error("保存失败！");
				}
			}
		}
	}
	
	/**
	 * 用户退出登陆
	 */
	public function logout(){
	    unset($_SESSION["user"]);
	    $url = U("Portal/Index/index");
	    header("location:$url");
	    exit;
	}
	

}
    
