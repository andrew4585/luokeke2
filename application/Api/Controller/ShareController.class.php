<?php
/**
 * 功    能：分享
 */
namespace Api\Controller;

class ShareController extends OauthController {
	
	public $type; 			//分享平台,1:sina,2:qq
	
	public $table;			//内容表（对于数据库）
	
	//内容表名称
	public $tblName	=	array(	'Group'		=> '套系',
								'Pcustom'	=> '客照',
								'Photo'		=> '作品',
								'Dress'		=> '婚纱礼服',
								'Article'	=> '婚嫁常识');	
	
	public $id;				//数据编号
	
	public $user;			//用户信息
	
	public $AppKey;
	
	public $AppSecret;
	
	public $token;
	
	public $callback;		//返回地址
	
	public $weibo	= array("SINA"=>"新浪微博","QQ"=>"腾讯微博");
	
	function _initialize() {
		$this->table 	= I("get.table");
		$this->id	 	= I("get.id",0,'intval');
		$this->callback	= $this->_getUri();
		$this->token	= $_SESSION['sina_token'];
// 		if(empty($this->id)){
// 			exit("数据编号丢失");
// 		}
	}
	
	/**
	 * 新浪微博分享
	 */
	function sina(){
		$this->type = 'SINA';
		$this->getConfig();
		//判断新浪微博是否已经登陆
		if(!empty($this->token)){
			$this->share();
		}
		
		if (isset($_REQUEST['code'])) {
			//获取accessToken
			$keys = array();
			$keys['code'] = $_REQUEST['code'];
			$keys['redirect_uri'] = $this->callback;
			try {
				vendor("sina.sina");
				$o			 = new \SaeTOAuthV2($this->AppKey, $this->AppSecret);
				$this->token = $o->getAccessToken( 'code', $keys ) ;
			} catch (OAuthException $e) {
				exit($e->getMessage());
			}
		}else{
			//新浪微博登陆
			$this->OAuthor();
		}
		//判断是否授权成功
		if ($this->token) {
			$_SESSION['sina_token'] = $this->token;
			$this->share();
		}else{
			exit("授权失败！");
		}
		
	}
	
	/**
	 * 腾讯微博分享
	 */
	function tencent(){
		$this->type = 'QQ';
		$this->getConfig();
		$this->OAuthor();
	}
	
	/**
	 * 第三方登陆
	 * @throws \Exception
	 */
	function OAuthor(){
		try{
			$this->user = session("user");
			//用户未登陆
			if(empty($this->user)){
				$url = U("User/Login/index");
				header("location:$url");
				exit;
			}else{
				switch ($this->type){
					//新浪微博登陆
					case 'SINA':
						vendor("sina.sina");
						$o			= new \SaeTOAuthV2($this->AppKey, $this->AppSecret);
						$callback	= $this->_getUri();
						$code_url 	= $o->getAuthorizeURL( $callback );
						header("location:$code_url");
						exit;
					//qq互联登陆
					case 'QQ':
						
						exit;
					default:
						throw new \Exception("非法操作");
				}
			}
		}catch(\Exception $e){
			Log::record("OAuthor:".$e->getMessage());
			exit($e->getMessage());
		}
	}
	
	/**
	 * 分享积分
	 */
	function share(){
		try{
			$model_score = D("Score");
			$model_config= D("Config"); 
			$model_user	 = D("User");
			$data['user_id']	= $this->user['user_id'];
			$data['score']		= $model_config->val("pc_share");
			$data['total_score']= $model_user->where("user_id={$data['user_id']}")->getField("score")
									+ $data['score'];
			$data['type']		= "分享".$this->tblName[$this->table]."到".$this->weibo[$this->type];
			$data['add_time']	= time();
			$data['today']		= date("Y-m-d");
			$result = $model_score->add($data);
			if($result){
				$this->assign("msg","分享成功");
			}else{
				$this->assign("msg","分享失败");
			}
			$this->display("share");
		}catch(\Exception $e){
			Log::record("OAuthor:".$e->getMessage());
			exit($e->getMessage());
		}
	}
	
	
	/**
	 * 获取应用配置信息
	 * @throws Exception
	 */
	function getConfig(){
		
		$config = C("THINK_SDK_{$this->type}");
		if(empty($config['APP_KEY']) || empty($config['APP_SECRET'])){
			exit('请配置您申请的APP_KEY和APP_SECRET');
		} else {
			$this->AppKey    = $config['APP_KEY'];
			$this->AppSecret = $config['APP_SECRET'];
		}
	}
	
	/**
	 * 检查是否登陆
	 */
	public function checkLogin(){
		$user_id = session("user.id");
		if(empty($user_id)){
			$this->error('未登陆');
		}else{
			$this->success("已登陆");
		}
	}
	
	/**
	 * 获取当前页面网址
	 */
	public function _getUri(){
		$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		return $url;
	}
}