<?php
/**
 * 会员注册
 */
namespace User\Controller;
use Common\Controller\HomeBaseController;
class RegisterController extends HomeBaseController {
	
	function index(){
		$this->display(":register");
	}
	
	function doregister(){
    	$users_model=M("Users");
    	$rules = array(
    			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
    			array('username', 'require', '账号不能为空！', 1 ),
    			array('mobile', 'require', '手机号不能为空！', 1 ),
    			array('password','require','密码不能为空！',1),
    			array('repassword', 'require', '重复密码不能为空！', 1 ),
    			array('repassword','password','确认密码不正确',0,'confirm'),
    			array('mobile','#^13[d]{9}$|14^[0-9]d{8}|^15[0-9]d{8}$|^18[0-9]d{8}$#','手机号格式不正确！',1), // 验证phone字段格式是否正确
    			 
    	);
    	if($users_model->validate($rules)->create()===false){
    		$this->error($users_model->getError());
    	}
    	
    	extract($_POST);
    	//用户名需过滤的字符的正则
    	$stripChar = '?<*.>\'"';
    	if(preg_match('/['.$stripChar.']/is', $username)==1){
    		$this->error('用户名中包含'.$stripChar.'等非法字符！');
    	}
    	
    	$banned_usernames=explode(",", sp_get_cmf_settings("banned_usernames"));
    	
    	if(in_array($username, $banned_usernames)){
    		$this->error("此用户名禁止使用！");
    	}
    	
    	if(strlen($password) < 5 || strlen($password) > 20){
    		$this->error("密码长度至少5位，最多20位！");
    	}
    	//需要获取到的短信验证码验证规则
    	/* if($messcode != ){
    		$this->error("短信验证码不正确，请重新验证！");
    	} */
		
    	$where['user_login']=$username;
    	$where['user_phone']=$mobile;
    	$where['_logic'] = 'OR';
    	$users_model=M("Users");
    	$result = $users_model->where($where)->count();
    	if($result){
    		$this->error("用户名或者该手机号已经存在！");
    	}else{
    		$data=array(
    			'user_login' => $username,
    			'user_phone' => $mobile,
    			'user_pass' => sp_password($password),
    			'last_login_ip' => get_client_ip(),
    			'create_time' => date("Y-m-d H:i:s"),
    			'last_login_time' => date("Y-m-d H:i:s"),
    			"user_type"=>2,
    		);
    		$rst = $users_model->add($data);
    		if($rst){
    			//登入成功页面跳转
    			$data['id']=$rst;
    			$_SESSION['user']=$data;
    			$this->success("注册成功！",__ROOT__."/");
    		}else{
    			$this->error("注册失败！",U("user/register/index"));
    		}
		}
	}
	
}