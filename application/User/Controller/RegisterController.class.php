<?php
/**
 * 会员注册
 */
namespace User\Controller;
use Common\Controller\HomeBaseController;
use Portal\Controller\IndexController;
class RegisterController extends IndexController {
	
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
    	if(!preg_match("/^1\d{10}$/",$mobile)){
    		$this->error('手机号码格式不正确');
    	}
    	
    	//需要获取到的短信验证码验证规则
    	$verifyCode = session("registerSMS");;
    	if($messcode != $verifyCode){
    		$this->error("短信验证码不正确，请重新验证！");
    	}
		
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
	
	/**
	 * 发送注册验证码
	 */
	public function sendRegisterSMS(){
		try{
			$user_phone=I("post.user_phone");
			$model_user=D("Users");
			$hasUser=$model_user->where("user_phone='$user_phone'")->find();
			if($hasUser)$this->error("该手机号已经注册");
			$code=rand(1000, 9999);
			$content="您好，您的验证码是:".$code.",感谢您注册大连洛可可婚纱摄影，如非本人操作请忽略【大连洛可可婚纱摄影】";
			$reply=$this->authcode($user_phone, $content);
			session("registerSMS",$code);
			$this->success($reply);
		}catch (\Exception $e){
			$this->error($e->getMessage());
		}
	}
	
	/**
	 *发送验证码
	 *@param string $user_name 用户名(即手机号)
	 *@param string $content   短信内容
	 */
	private function authcode($user_name,$content){
		$url="http://api.chanyoo.cn/utf8/interface/send_sms.aspx";
		$data=array(
				"username"=>"rococo",
				"password"=>"5843385",
				"receiver"=>$user_name,
				"content"=>$content
		);
		$reply=http($url, $data,"POST");
		return $reply;
	}
}