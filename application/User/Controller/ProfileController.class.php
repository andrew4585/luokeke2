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
    		$repassword=$_POST['repassword'];
    		$password=$_POST['password'];
			if($repassword == $password){
				$data['user_pass'] 	= sp_password($password);
				$data['id'] 		= $uid;
				$res = $this->users_model->save($data);
				if($res){
					$this->success("修改成功！");
				}else{
					$this->error("修改成功!");
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
		$user=$this->users_model->where(array("id"=>$userid))->find();
		$this->assign($user);
    	$this->display();
    }

    function avatar_upload(){

		if(IS_POST){
			try{
				$userid		= session("user.id");
				$file_src 	= "src.png";
				$upload		= "./data/upload/avatar/$userid";
				$filename162= $upload."_1.png";
				$filename48 = $upload."_2.png";
				$filename20 = $upload."_3.png";

				$src=base64_decode($_POST['pic']);
				$pic1=base64_decode($_POST['pic1']);
				$pic2=base64_decode($_POST['pic2']);
				$pic3=base64_decode($_POST['pic3']);

				if($src) {
					file_put_contents($file_src,$src);
				}

				file_put_contents($filename162,$pic1);
				file_put_contents($filename48,$pic2);
				file_put_contents($filename20,$pic3);

				$rs['status'] = 1;
				echo json_encode($rs);
			}catch (\Exception $e){
				Log::record("头像上传失败：".$e->getMessage());
				$rs['status'] = $e->getMessage();
				echo json_encode($rs);
			}

		}
    }
    function usercontact(){
    	$userid=sp_get_current_userid();
    	$user=$this->users_model->where(array("id"=>$userid))->find();
    	$this->assign($user);
    	$this->assign("servePromise",$this->_getAd("servePromise"));
    	$this->display();
    }
    
}
    
