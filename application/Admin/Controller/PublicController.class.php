<?php

/**
 */
namespace Admin\Controller;
use Think\Verify;

use Common\Controller\AdminbaseController;
class PublicController extends AdminbaseController {

    function _initialize() {}
    
    //后台登陆界面
    public function login() {
    	if(isset($_SESSION['ADMIN_ID'])){//已经登录
    		$this->success(L('LOGIN_SUCCESS'),U("Index/index"));
    	}else{
    		if(empty($_SESSION['adminlogin'])){
    			redirect(__ROOT__."/");
    		}else{
    			$this->display(":login");
    		}
    		
    	}
    }
    
    public function logout(){
    	session('ADMIN_ID',null); 
    	$this->redirect("public/login");
    }
    
    public function dologin(){
    	$name = I("post.username");
    	$this->err_msg = '';
    	if(empty($name)){
    		$this->err_msg = L('USERNAME_OR_EMAIL_EMPTY');
    	}
    	$pass = I("post.password");
    	if(empty($pass)){
    		$this->err_msg = L('PASSWORD_REQUIRED');
    	}
    	$verrify = I("post.verify");
    	if(empty($verrify)){
    		$this->err_msg = L('CAPTCHA_REQUIRED');
    	}
    	//验证码
    	if(!$this->check_verify($verrify)){
    		$this->err_msg = L('CAPTCHA_NOT_RIGHT');
    	}
    	if(!empty($this->err_msg)){
    		$this->assign("err_msg",$this->err_msg);
    		$this->display(":login");exit;
    	}
    	$user = D("AdminUser");
    	$where['user_login']=$name;
    	
    	$result = $user->where($where)->find();
    	if($result != null){
    		if($result['user_pass'] == sp_password($pass)){
    			//登入成功页面跳转
    			$_SESSION["ADMIN_ID"]=$result["id"];
    			$_SESSION['name']=$result["user_login"];
    			session("roleid",$result['role_id']);
    			$result['last_login_ip']=get_client_ip();
    			$result['last_login_time']=date("Y-m-d H:i:s");
    			$user->save($result);
    			setcookie("admin_username",$name,time()+30*24*3600,"/");
    			$this->redirect("Index/index");
    		}else{
    			$this->err_msg = L('PASSWORD_NOT_RIGHT');
    		}
    	}else{
    		$this->err_msg = L('USERNAME_NOT_EXIST');
    	}
    	$this->assign("err_msg",$this->err_msg);
    	$this->display(":login");
    }

    /**
     * 检测验证码
     */
    function check_verify($code, $id = 1){
    	$verify = new Verify();
    	return $verify->check($code, $id);
    }
}

