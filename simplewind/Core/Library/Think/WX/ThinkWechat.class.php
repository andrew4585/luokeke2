<?php
use Think\Log;

class ThinkWechat {
	public $error;
	/**
	 * 微信推送过来的数据或响应数据
	 * @var array
	 */
	private $data = array();
	
	/**
	 * 主动发送的数据
	 * @var array
	 */
	private $send = array();
	
	protected $appid;
	
	protected $appsecret;
	
	public function __construct($appid,$appsecret){
	    $this->appid       = $appid;
	    $this->appsecret   = $appsecret;
	}
		
	/**
	 * 获取微信推送的数据
	 * @return array 转换为数组后的数据
	 */
	public function request(){
		$this->auth() || exit;
		if(IS_GET){
			exit($_GET['echostr']);
		} else {
			$xml = file_get_contents("php://input");
			$xml = new SimpleXMLElement($xml);
			$xml || exit;
			foreach ($xml as $key => $value) {
				$this->data[$key] = strval($value);
			}
		}
       	return $this->data;
	}

	/**
	 * * 被动响应微信发送的信息（自动回复）
	 * @param  string $to      接收用户名
	 * @param  string $from    发送者用户名
	 * @param  array  $content 回复信息，文本信息为string类型
	 * @param  string $type    消息类型
	 * @param  string $flag    是否新标刚接受到的信息
	 * @return string          XML字符串
	 */
	public function response($content, $type = 'text', $flag = 0){
		/* 基础数据 */
		$this->data = array(
			'ToUserName'   => $this->data['FromUserName'],
			'FromUserName' => $this->data['ToUserName'],
			'CreateTime'   => NOW_TIME,
			'MsgType'      => $type,
		);

		/* 添加类型数据 */
		$this->$type($content);

		/* 添加状态 */
		$this->data['FuncFlag'] = $flag;

		/* 转换数据为XML */
		$xml = new SimpleXMLElement('<xml></xml>');
		$this->data2xml($xml, $this->data);
		exit($xml->asXML());
	}

	/**
	 * * 主动发送消息
	 *
	 * @param string $content   内容
	 * @param string $openid   	发送者用户名
	 * @param string $type   	类型
	 * @return array 返回的信息
	 */
	
	public function sendMsg($content, $openid = '', $type = 'text') {
		/* 基础数据 */
		$this->send ['touser'] = $openid;
		$this->send ['msgtype'] = $type;
		$this->data['ToUserName']=$openid;
		/* 添加类型数据 */
		$sendtype = 'send' . $type;
		$this->$sendtype ( $content );
		/* 发送 */
// 		$sendjson = jsencode ( $this->send );
		$sendjson = json_encode($this->send,JSON_UNESCAPED_UNICODE );
		$restr = $this->send ( $sendjson );
		return $restr;
	}
	
	/**
	 * 发送文本消息
	 * @param string $content	要发送的信息
	 */
	private function sendtext($content) {
		$this->send ['text'] = array (
				'content' => $content 
		);
	}
	
	/**
	 * 发送图片消息
	 * @param string $content	要发送的信息
	 */
	private function sendimage($content) {
		$this->send ['image'] = array (
				'media_id' => $content 
		);
	}

	/**
	 * 发送视频消息
	 * @param  string $content 要发送的信息
	 */
	private function sendvideo($video){
		list (
			$video ['media_id'],
			$video ['title'],
			$video ['description']
		) = $video;
		
		$this->send ['video'] = $video;
	}
	
	/**
	 * 发送语音消息
	 * @param string $content	要发送的信息
	 */
	private function sendvoice($content) {
		$this->send ['voice'] = array (
				'media_id' => $content 
		);
	}
	
	/**
	 * 发送音乐消息
	 * @param string $content	要发送的信息
	 */
	private function sendmusic($music) {
		list ( 
			$music ['title'], 
			$music ['description'], 
			$music ['musicurl'], 
			$music ['hqmusicurl'], 
			$music ['thumb_media_id']
		) = $music;
		
		$this->send ['music'] = $music;
	}
	
	/**
	 * 发送图文消息
	 * @param  string $news 要回复的图文内容
	 */
	private function sendnews($news){
		$model_system=D("WxConfig");
		$web=$model_system->val("web");
		$articles = array();
		foreach ($news as $key => $value) {
			$articles[$key]['title']=$value['post_title'];
			$articles[$key]['description']=$value['post_excerpt'];
			if(empty($value['post_url'])){
				//需要修改
				$articles[$key]['url']=$web."/index.php/Api/Article/index/article_id/".$value['article_id']."/id/".$this->data['ToUserName'];
			}else{
				$articles[$key]['url']=$value['post_url'];
			}
			$articles[$key]['picurl']=$web.$value['posst_pic'];
			if($key >= 9) { break; } //最多只允许8条新闻
		}
		$this->send['news']['articles'] = $articles;
	}
	
	
	/**
	 * 获取微信用户的基本资料
	 * @param string $openid   	发送者用户名
	 * @return array 用户资料
	 */
	public function user($openid = '') {
		if ($openid) {
			header ( "Content-type: text/html; charset=utf-8" );
			$url = 'https://api.weixin.qq.com/cgi-bin/user/info';
			$params = array ();
			$params ['access_token'] = $this->getToken ();
			$params ['openid'] = $openid;
			$httpstr = http ( $url, $params );
			$harr = json_decode ( $httpstr, true );
			return $harr;
		} else {
			return false;
		}
	}
	
	/**
	 * 生成菜单
	 * @param  string $data 菜单的str
	 * @return string  返回的结果；
	 */
	public function setMenu($data = NULL){
		$access_token = $this->getToken();
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
		$menustr = http($url, $data, 'POST', array("Content-type: text/html; charset=utf-8"), true);
		return $menustr;
	}

	/**
	 * 回复文本信息
	 * @param  string $content 要回复的信息
	 */
	private function text($content){
		$this->data['Content'] = $content;
	}
	/**
	 * 触发多客服
	 * @param unknown_type $content
	 * @return boolean
	 */
	private function transfer_customer_service($content){
		return false;
	}
	/**
	 * 回复音乐信息
	 * @param  string $content 要回复的音乐
	 */
	private function music($music){
		list(
			$music['Title'], 
			$music['Description'], 
			$music['MusicUrl'], 
			$music['HQMusicUrl']
		) = $music;
		$this->data['Music'] = $music;
	}

	/**
	 * 回复图文信息
	 * @param  string $news 要回复的图文内容
	 */
	private function news($news){
		$model_system=D("WxConfig");
		$web=$model_system->val("web");
		$articles = array();
		foreach ($news as $key => $value) {
			$articles[$key]['Title']=$value['title'];
			$articles[$key]['Description']=$value['brief'];
			$articles[$key]['PicUrl']=$web."/Upload/article/".$value['pic'];
			if(empty($value['url'])){
				$articles[$key]['Url']=$web."/index.php/Api/Article/index/article_id/".$value['article_id']."/id/".$this->data['ToUserName'];			
			}else{
				$articles[$key]['Url']=$value['url'];
			}
			if($key >= 9) { break; } //最多只允许10调新闻
		}
		$this->data['ArticleCount'] = count($articles);
		$this->data['Articles'] = $articles;
	}
		
	/**
	 * 主动发送的信息
	 * @param  string $data    json数据
	 * @return string          微信返回信息
	 */
	private function send($data = NULL) {
		$access_token = $this->getToken ();
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
		$restr = http ( $url, $data, 'POST', array ( "Content-type: text/html; charset=utf-8" ), true );
		return $restr;
	}

	/**
     * 数据XML编码
     * @param  object $xml  XML对象
     * @param  mixed  $data 数据
     * @param  string $item 数字索引时的节点名称
     * @return string
     */
    private function data2xml($xml, $data, $item = 'item') {
        foreach ($data as $key => $value) {
            /* 指定默认的数字key */
            is_numeric($key) && $key = $item;

            /* 添加子元素 */
            if(is_array($value) || is_object($value)){
                $child = $xml->addChild($key);
                $this->data2xml($child, $value, $item);
            } else {
            	if(is_numeric($value)){
            		$child = $xml->addChild($key, $value);
            	} else {
            		$child = $xml->addChild($key);
	                $node  = dom_import_simplexml($child);
				    $node->appendChild($node->ownerDocument->createCDATASection($value));
            	}
            }
        }
    }

    /**
	 * 对数据进行签名认证，确保是微信发送的数据
	 * @param  string $token 微信开放平台设置的TOKEN
	 * @return boolean       true-签名正确，false-签名错误
	 */
	private function auth(){
		$model_system=D("WxConfig");
		$token=$model_system->val("token");
		
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];
		
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
		
	/**
	 * 获取保存的accesstoken
	 */
	private function getToken() {
		$cache_name=C("CACHE_PREFIX")."S_TOKEN";
		$stoken = array ();
		$stoken = S($cache_name); // 从缓存获取ACCESS_TOKEN
		if (is_array ( $stoken )) {
			$nowtime = time ();
			$difftime = $nowtime - $stoken ['tokentime']; // 判断缓存里面的TOKEN保存了多久；
			if ($difftime > 7000||empty($stoken ['token'])) { // TOKEN有效时间7200 判断超过7000就重新获取;
				$accesstoken = $this->getAcessToken (); // 去微信获取最新ACCESS_TOKEN
				$stoken ['tokentime'] = time ();
				$stoken ['token'] = $accesstoken;
				S ( $cache_name, $stoken ); // 放进缓存
			} else {
				$accesstoken = $stoken ['token'];
			}
		} else {
			$accesstoken = $this->getAcessToken (); // 去微信获取最新ACCESS_TOKEN
			$stoken ['tokentime'] = time ();
			$stoken ['token'] = $accesstoken;
			S ( $cache_name, $stoken ); // 放进缓存
		}
		
		return $accesstoken;
	}
	/**
	 * 重新从微信获取accesstoken
	 */
	private function getAcessToken() {
		$url = 'https://api.weixin.qq.com/cgi-bin/token';
		$params = array ();
		$params ['grant_type'] = 'client_credential';
		$params ['appid'] = $this->appid;
		$params ['secret'] = $this->appsecret;
		$httpstr = http ( $url, $params );
		$harr = json_decode ( $httpstr, true );
		return $harr ['access_token'];
	}
	/*****************************************************用户相关*******************************
	/**
	 * 获取openid列表
	 */
	public function getOpenIdList($next_openid=''){
		$access_token = $this->getToken ();
		$url="https://api.weixin.qq.com/cgi-bin/user/get";
		$data=array(
				'access_token'=>$access_token,
				'next_openid'=>$next_openid
		);
		$restr= http ( $url, $data, 'GET', array ( "Content-type: text/html; charset=utf-8" ), true );
		return $restr;
	}
	/**
	 * 查询所有分组
	 * @return Ambigous <multitype:, mixed>
	 */
	public function check_allgroup(){
		$access_token = $this->getToken();
		$url="https://api.weixin.qq.com/cgi-bin/groups/get";
		$data=array(
				'access_token'=>$access_token,
		);
		$restr= http ( $url, $data, 'GET', array ( "Content-type: text/html; charset=utf-8" ), true );
		return $restr;
	}
	/**
	 * 创建分组
	 * @param unknown_type $name
	 * @return Ambigous <multitype:, mixed>
	 */
	public function create_group($name){
		$access_token = $this->getToken();
		$url="https://api.weixin.qq.com/cgi-bin/groups/create?access_token=$access_token";
		$data=array(
				'group'=>array("name"=>urlencode($name)),
		);
		$data=json_encode($data);
		$data=urldecode($data);
		$restr= http ( $url, $data, 'POST', array ( "Content-type: text/html; charset=utf-8" ), true );
		return $restr;
	}
	/**
	 * 修改分组
	 * @param unknown_type $data
	 * @return Ambigous <multitype:, mixed>
	 */
	public function update_group($data){
		$access_token = $this->getToken();
		$url="https://api.weixin.qq.com/cgi-bin/groups/update?access_token=$access_token";
		$data=array(
				'group'=>array("id"=>$data['id'],"name"=>urlencode($data['name'])),
		);
		$data=json_encode($data);
		$data=urldecode($data);
		$restr= http ( $url, $data, 'POST', array ( "Content-type: text/html; charset=utf-8" ), true );
		return $restr;
	}
	
	/**
	 * 删除用户分组
	 * @param unknown $data
	 */
	public function delete_group($data){
	    $access_token = $this->getToken();
	    $url="https://api.weixin.qq.com/cgi-bin/groups/delete?access_token=$access_token";
	    $data=array(
	        'group'=>array("id"=>$data['id']),
	    );
	    $data=json_encode($data);
	    $data=urldecode($data);
	    $restr= http ( $url, $data, 'POST', array ( "Content-type: text/html; charset=utf-8" ), true );
	    return $restr;
	}
	/**
	 * 获取用户所在分组
	 * @param unknown_type $openid
	 * @return Ambigous <multitype:, mixed>
	 */
	public function getUserGroupid($openid){
		$access_token = $this->getToken();
		$url="https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=$access_token";
		$data='{"openid":"'.$openid.'"}';
		$restr= http ( $url, $data, 'POST', array ( "Content-type: text/html; charset=utf-8" ), true );
		return $restr;
	}
	/**
	 * 修改用户分组
	 */
	public function changeUserGroup($openid,$groupid){
		$access_token = $this->getToken();
		$url="https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=$access_token";
		$data='{"openid":"'.$openid.'","to_groupid":'.$groupid.'}';
		$restr= http ( $url, $data, 'POST', array ( "Content-type: text/html; charset=utf-8" ), true );
		return $restr;
	}
	/*************************************************用户相关 end *************************
	/**
	 * 上传图片文件给微信
	 */
	public function uploadMedia($file_name){
		$root=str_ireplace(str_replace("/","\\",$_SERVER['PHP_SELF']),'',__FILE__)."\\";
		$file="@$root".C("APP_BASE")."Upload/article/".$file_name;
		$data=array('file'=>$file);
		$access_token = $this->getToken ();
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type=image";
		$restr = http ( $url, $data, 'POST', array ( "Content-type: text/html; charset=utf-8" ), true );
		return $restr;
	}
	
	/**
	 * 群发文本信息
	 * @param unknown_type $content
	 * @return int $i   发送的用户分组的个数
	 */
	public function mass_text($content,$groups){
		$access_token = $this->getToken ();
		$url="https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=$access_token";
		$i=0;
		foreach($groups as $item){
// 			$data='{
// 				"filter":{
// 					"group_id":"'.$item.'"
// 				},
// 				"text":{
// 					"content":"'.$content.'"
// 				},
// 				 "msgtype":"text"
// 			}
// 			';
			$data=array(
					"filter"=>array("group_id"=>$item),
					"text"=>array("content"=>urlencode($content)),
					"msgtype"=>"text"
					);
			/*$data=json_encode($data);
			$data=urldecode($data);
			Think\Log::record($data,'WARN');*/
			$restr = http ( $url, $data, 'POST', array ( "Content-type: text/html; charset=utf-8" ), true );
			Think\Log::record($restr,'WARN');
			$restr = json_decode($restr,true);
			//Think\Log::record($restr,'WARN');
			if($restr['errcode']==0)$i++;
		}
		return $i;
	}
	/**
	 * 群发图文信息
	 * @param unknown_type $article_id
	 */
	public function mass_news($article_id,$groups){
		$model_art=D("Article");
		$model_system=D("SystemInfo");
// 		$model_user=D("User");
		$needle=strstr($article_id,",");
		$media_data["articles"]=array();
		$web=$model_system->getValue("web");
		if ($needle){	//含有多个id
			$artidList=explode(",", $article_id);
			foreach ($artidList as $item){
				$article=$model_art->where("article_id=$item")->find();
				$media_id=$this->create_img_media_id($article['pic']);
				if($media_id){
					$media=$this->create_news_media_data($media_id, $article,$web);
					array_push($media_data["articles"], $media);
				}
			}
		}else{			//只有一个id
			$article=$model_art->where("article_id=$article_id")->find();
			$media_id=$this->create_img_media_id($article['pic']);
			if($media_id){
				$media=$this->create_news_media_data($media_id, $article,$web);
				array_push($media_data["articles"], $media);
			}
		}
		$media_data=json_encode($media_data);
		$media_data=urldecode($media_data);
		$news_media_id=$this->create_news_media($media_data);
		if($news_media_id){
			$access_token = $this->getToken ();
			$url="https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=$access_token";
			$i=0;
			foreach($groups as $item){
				$data=array(
						"filter"=>array("group_id"=>$item),
						"mpnews"=>array("media_id"=>$news_media_id),
						"msgtype"=>"mpnews"
						);
				$data=json_encode($data);
				$restr = http ( $url, $data, 'POST', array ( "Content-type: text/html; charset=utf-8" ), true );
				$restr = json_decode($restr,true);
				if($restr['errcode']==0)$i++;
			}
			return $i;
		}else{
			return 0;
		}
	}
	
	/**
	 * 编写需要发送的图文素材
	 * @param unknown_type $media_id
	 * @param unknown_type $article
	 * @return number
	 */
	public function create_news_media_data($media_id,$article,$web){
		if(empty($article['url'])){
			$url=$web."/index.php/Api/Article/index/article_id/".$article['article_id'];
		}else{
			$url=$article['url'];
		}
		$data=array(
				"thumb_media_id"	=>	$media_id,
				"author"			=>	"",
				"title"				=>	urlencode($article['title']),
				"content_source_url"=>	$url,
				"content"			=>	" ",
				"digest"			=>	urlencode($article['brief']),
				"show_cover_pic"	=>	1
				);
		return $data;
	}
	/**
	 * 上传图文素材,生成图文素材编号
	 * @param unknown_type $data
	 * @return mixed|boolean
	 */
	public function create_news_media($data){
		$access_token = $this->getToken ();
		$url="https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=$access_token";
		$restr = http ( $url, $data, 'POST', array ( "Content-type: text/html; charset=utf-8" ), true );
		$restr = json_decode($restr,true);
		if(!empty($restr['media_id'])){
			return $restr['media_id'];
		}else{
			$this->error=$restr['errmsg'];
			return false;
		}
	}
	/**
	 * 上传图片，生成图片编号
	 * @param unknown_type $img
	 * @return mixed|boolean
	 */
	public function create_img_media_id($img){
		$access_token = $this->getToken ();
		$url="http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=$access_token&type=image";
		$filepath=$_SERVER['DOCUMENT_ROOT'].C("APP_BASE")."Upload/article/".$img;
		$data=array("media"=>"@".$filepath);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$restr = curl_exec($ch);
		curl_close($ch);
		$restr = json_decode($restr,true);
		if(!empty($restr['media_id'])){
			return $restr['media_id'];
		}else{
			$this->error=$restr['errmsg'];
			return false;
		}
	}
}
