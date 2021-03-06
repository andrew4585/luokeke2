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
								'Article'	=> '婚嫁常识',
	                            'wx_article'=> '微信文章'
	);	
	
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
			$sharecomment = "{$_REQUEST['sharecomment']}http://".$_SERVER['HTTP_HOST']."/Portal/{$this->table}/info/id/{$this->id}";
			$sharecomment = urlencode($sharecomment);
			$ret = $c->upload( $sharecomment,'http://'.$_SERVER['HTTP_HOST'].$_REQUEST['picurl']);
			//$ret = $c->upload( $_REQUEST['sharecomment'],'http://'.$_SERVER['HTTP_HOST'].$_REQUEST['picurl']);
			if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
				echo "<p>发送失败，错误：{$ret['error_code']}:{$ret['error']}</p>";
			} else {
				$this->share();
			}
			exit;
		}
		
		$this->callback .= "/sharecomment/{$_REQUEST['sharecomment']}/picurl/{$_REQUEST['picurl']}"; 
		if (isset($_REQUEST['code'])) {
			//获取accessToken
			$keys = array();
			$keys['code'] = $_REQUEST['code'];
			$keys['redirect_uri'] = $this->callback;
			try {
				vendor("sina.sina");
				$o			 = new \SaeTOAuthV2($this->AppKey, $this->AppSecret);
				$this->token = $o->getAccessToken( 'code', $keys ) ;
			} catch (\OAuthException $e) {
				exit($e->getMessage());
			}
		}else{
			//新浪微博登陆
			$this->OAuthor();
		}
		//判断是否授权成功
		if ($this->token) {
			$_SESSION['sina_token'] = $this->token;
			header("Content-type:text/html;charset=utf-8");
			echo "新浪微博登陆成功，请重新分享";
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
		vendor('qq.qq');
		$QC = new \QC();
		$QC->recorder->write("appid", $this->AppKey);
		$QC->recorder->write("appkey", $this->AppSecret);
		$QC->recorder->write("callback", 'http://'.$_SERVER['HTTP_HOST']."/Api/Share/tencent");
		$QC->reInit();
		
		//由于qq互联奇葩的回调地址规则，必须要完整的回调地址并且不能带有参数
		//所以将参数以session的形式存储
		//作者按：腾讯你个sb
		if($_REQUEST['picurl'])
		    session("share_picurl",$_REQUEST['picurl']);
		if($_REQUEST['sharecomment'])
		    session("share_sharecomment",$_REQUEST['sharecomment']);
		if($_GET['table'])
		    session("share_table",$this->table);
		if($_GET['id'])
		    session("share_id",$this->id);
		//==============================================
		
		if (empty($QC->recorder->read("access_token"))
		    ||empty($QC->recorder->read("openid"))){
		    if ($_GET['code']) {//已获得code
		        $QC->qq_callback();
		        $QC->get_openid();
		        $QC->reInit();
		        $_FILES['pic']="@.".session("share_picurl");
		        $_POST['content'] = session("share_sharecomment")."http://".$_SERVER['HTTP_HOST']."/Portal/".session("share_table")."/info/id/".session("share_id");
		        $ret = $QC->add_pic_t($_POST);
		        if($ret['ret'] == 0){
		            $this->table=session("share_table");
		            $this->id = session("share_id");
		            $this->share();
		        }else{
		            alert("发表失败");
		        }
		    } else {//获取授权code
		        $QC->qq_login();
		    }
		}else{
		    $_FILES['pic']="@.".session("share_picurl");
		    $_POST['content'] = session("share_sharecomment");
			$ret = $QC->add_pic_t($_POST);
			if($ret['ret'] == 0){
			    $this->share();
			}else{
			    alert("发表失败");
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
			//分享数+1
			$result	= $contentModel->where("id={$this->id}")->setInc("post_share",1);
			if(!$result) alert("分享失败");
				
			$this->display("share");
			if(!empty($_SESSION['user'])){
				$model_score = D("Exchange");
				$model_config = D("Config");
				
				//获取今天用户的分享总积分
				$stime = strtotime(date('Y-m-d 00:00:00'));
				$etime = strtotime(date('Y-m-d 23:59:59'));
				$sumWhere = "uid = {$_SESSION['user']['id']}
				              and type=3
				              and post_date>$stime
				              and post_date<$etime";
				$total = $model_score->where($sumWhere)->sum("point");
				$share_max = $model_config->val("share_max");
				if($total>=$share_max) alert("您已经达到每日分享积分的上限");
				
				$model_user = D("Users");
				$data = array(
				    "uid"        =>  $_SESSION['user']['id'],
				    "type"       =>  3,
				    "post_table" =>  $this->table,
				    "post_id"    =>  $this->id,
				    "point"      =>  $model_config->val('pc_share'),
				    "memo"       =>  "分享".$this->tblName[$this->table]."到".$this->weibo[$this->type],
				    'post_date'  =>  time()
				);
				$findWhere = " uid={$data['uid']}           AND
				               post_id={$data['post_id']}   AND
				               post_table='{$this->table}'    AND
				               type=3";
				//检查是否已经分享
				$hasExchange  = $model_score->where($findWhere)->find();
				if($hasExchange){
				    alert("您已经分享该".$this->tblName[$this->table]);
				}
				$sumPoint = $model_score->where("uid={$data['uid']}")->order('post_date desc')->limit(1)->find();
				$data['sumpoint'] = $sumPoint['sumpoint']+$data['point'];
				$result = $model_score->add($data);
				if($result){
				    $model_user->where("id={$data['uid']}")->setInc("score",$data['point']);
				}
				alert("分享成功");
			}else{
			    alert("分享成功,登陆后分享可获取积分");
			}
			
		}catch(\Exception $e){
			Log::record("OAuthor:".$e->getMessage());

			exit($e->getMessage());
		}
	}
	
	public function wx_share_score(){
	    try {
	        if(!empty($this->table)){
	            $contentModel = D($this->table);
	            //分享数+1
	            $result	= $contentModel->where("id={$this->id}")->setInc("post_share",1);
	            if(!$result) $this->error("分享失败");
	        }else{
	            $this->table="wx_article";
	        }
	
	        $model_score = D("Exchange");
	        $model_config = D("Config");
	        $model_user = D("Users");
	        $point = $model_config->val('wx_share');
	        if(!$point) exit;
	        $data = array(
	            "uid"        =>  I("get.uid"),
	            "type"       =>  3,
	            "post_id"    =>  $this->id,
	            "post_table" =>  $this->table,
	            "point"      =>  $point,
	            "memo"       =>  "分享".$this->tblName[$this->table]."到朋友圈",
	            'post_date'  =>  time()
	        );
	        
	        //获取今天用户的分享总积分
	        $stime = strtotime(date('Y-m-d 00:00:00'));
	        $etime = strtotime(date('Y-m-d 23:59:59'));
	        $sumWhere = "uid = {$data['uid']}
                	        and type=3
                	        and post_date>$stime
                	        and post_date<$etime";
	        $total = $model_score->where($sumWhere)->sum("point");
	        $share_max = $model_config->val("share_max");
	        if($total>=$share_max) E("您已经达到每日分享积分的上限");
	        
	        $findWhere = " uid={$data['uid']}           AND
	        post_id={$data['post_id']}   AND
	        post_table='{$this->table}'    AND
	        type=3";
	        //检查是否已经分享
	        $hasExchange  = $model_score->where($findWhere)->find();
	        if($hasExchange)exit();
	
	        $sumPoint = $model_score->where("uid={$data['uid']}")->order('post_date desc')->limit(1)->find();
	        $data['sumpoint'] = $sumPoint['sumpoint']+$data['point'];
	        $result = $model_score->add($data);
	        if($result){
	            $model_user->where("id={$data['uid']}")->setInc("score",$data['point']);
	            $this->success("获得分享积分+".$point);
	        }
	        
	    } catch (\Exception $e) {
	        $this->error($e->getMessage());
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