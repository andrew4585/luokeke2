<?php
/**
 * 功    能：分享
 */
namespace Api\Controller;

use Think\Log;
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
			$_SESSION['sina_token'] = $this->token;
			vendor("sina.sina");
			$c = new \SaeTClientV2($this->AppKey, $this->AppSecret, $_SESSION['sina_token']['access_token']);
			$ret = $c->upload_url_text( $_REQUEST['sharecomment'],'http://'.$_SERVER['HTTP_HOST'].$_REQUEST['picurl']);
			if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
				echo "<p>发送失败，错误：{$ret['error_code']}:{$ret['error']}</p>";
			} else {
				echo "<p>发送成功</p>";
			}
			$this->share();
			exit;
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
			$c = new \SaeTClientV2($this->AppKey, $this->AppSecret, $_SESSION['sina_token']['access_token']);

			$ret = $c->upload_url_text( $_REQUEST['sharecomment'],'http://'.$_SERVER['HTTP_HOST'].$_REQUEST['picurl']);
			if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
				echo "<p>发送失败，错误：{$ret['error_code']}:{$ret['error']}</p>";
			} else {
				echo "<p>发送成功</p>";
			}
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
		vendor('qq.Tencent');
		\OAuth::init($this->AppKey,$this->AppSecret);
		if ($_SESSION['t_access_token'] || ($_SESSION['t_openid'] && $_SESSION['t_openkey'])){

		}else{
			$callback = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
			if ($_GET['code']) {//已获得code
				$code = $_GET['code'];
				$openid = $_GET['openid'];
				$openkey = $_GET['openkey'];
				//获取授权token
				$url = \OAuth::getAccessToken($code, $callback);
				$r = \Http::request($url);
				parse_str($r, $out);
				//存储授权数据
				if ($out['access_token']) {
					$_SESSION['t_access_token'] = $out['access_token'];
					$_SESSION['t_refresh_token'] = $out['refresh_token'];
					$_SESSION['t_expire_in'] = $out['expires_in'];
					$_SESSION['t_code'] = $code;
					$_SESSION['t_openid'] = $openid;
					$_SESSION['t_openkey'] = $openkey;

					//验证授权
					$r = \OAuth::checkOAuthValid();
					if ($r) {
						header('Location: ' . $callback);//刷新页面
					} else {
						exit('<h3>授权失败,请重试</h3>');
					}
				} else {
					exit($r);
				}
			} else {//获取授权code
				if ($_GET['openid'] && $_GET['openkey']){//应用频道
					$_SESSION['t_openid'] = $_GET['openid'];
					$_SESSION['t_openkey'] = $_GET['openkey'];
					//验证授权
					$r = \OAuth::checkOAuthValid();
					if ($r) {
						header('Location: ' . $callback);//刷新页面
					} else {
						exit('<h3>授权失败,请重试</h3>');
					}
				} else{
					$url =\OAuth::getAuthorizeURL($callback);
					header('Location: ' . $url);
				}
			}

		}
	}
	
	/**
	 * 第三方登陆
	 * @throws \Exception
	 */
	function OAuthor(){
		try{
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
					vendor('qq.Tencent');
					\OAuth::init($this->AppKey,$this->AppSecret);
					$oc = new \OAuth();
					exit;
				default:
					throw new \Exception("非法操作");
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
			$contentModel = D("{$this->table}");
			$result	= $contentModel->where("id={$this->id}")->setInc("post_share",1);
			if($result){
				$this->assign("msg","分享成功");
			}else{
				$this->assign("msg","分享失败");
			}
			$this->display("share");
			if(!empty($_SESSION['user'])){
				$model_score = D("Exchange");
				$model_config = D("Config");
				$model_user = D("Users");
				$data['uid'] = $_SESSION['user']['id'];
				$data['point'] = $model_config->val('pc_share');
				$sumPoint = $model_score->where("uid={$data['uid']}")->order('post_date desc')->limit(1)->find();
				dump($sumPoint);
				$data['sumpoint'] = $sumPoint['sumpoint']+$data['point'];
				$data['memo'] = "分享".$this->tblName[$this->table]."到".$this->weibo[$this->type];
				$data['type'] = 3;//为分享
				$data['post_date'] = time();
				$result = $model_score->add($data);
			}
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