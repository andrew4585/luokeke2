<?php
namespace Portal\Controller;

class PCustomController extends IndexController {
	
	protected $model_pcustom	;
	protected $model_cat	;
	
	public function __construct(){
		parent::__construct();
		$this->model_pcustom	= D("Pcustom");
	}
	
	/**
	 * 详细页
	 */
	public function info(){
		$id			= I("get.id",0,'intval');
		if(empty($id)){
			$this->error("参数丢失");
		}
		$where		= "id=$id and status=1 and cid=0";
		$info		= $this->model_pcustom->where($where)->find();
		if(!empty($info['post_url'])){
			header("location:".$info['post_url']);exit;
		}
		//上一组
		$prev	 	= $this->model_pcustom->where("id>$id and status=1 and cid=0")->order("id asc")->getField("id");
		//下一组
		$next		= $this->model_pcustom->where("id<$id and status=1 and cid=0")->order("id DESC")->getField("id");
		$this->assign("prev",$prev);
		$this->assign("next",$next);
		
		//banner
		$this->assign("home_head",$this->_getAd("banner_pcustom"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		//服务承诺
		$this->assign("servePromise",$this->_getAd("servePromise"));
		//广告位--蕴含美态
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		//详细内容
		$this->assign("info",$info);
		//评论信息
		$this->getCommentList("Pcustom",$info['id']);
		$this->assign("table",'Pcustom');
		//图片信息
		$photo	= json_decode($info['smeta'],true);
		$this->assign("photo",$photo['photo']);
		//获取本页面的url
		$this->assign("url",$this->_getUri());
		//生成二维码
		$this->qrcode();
		$this->assign("model_table","Pcustom");
		$this->display();
	}
	public function lists(){
		$this->_list($this->model_pcustom,true,array(),array("cid=0"),16,"recommended desc,listorder,id desc");
		$this->assign("ad_dress",$this->_getAd("dress"));
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		//banner
		$this->assign("home_head",$this->_getAd("banner_pcustom"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		//服务承诺
		$this->assign("servePromise",$this->_getAd("servePromise"));

		$this->display("list");
	}
	
	/**
	 * 用户点击‘喜欢’按钮
	 */
	public function ajax_like(){
		$this->_like($this->model_pcustom);
	}

	public function nav_index(){
		$nav_arr['name'] 	= "客片欣赏";
		$nav_arr['name_url'] = U("Portal/PCustom/lists");
		exit(json_encode($nav_arr));
	}
	
	//-----------------------------幸福抢先看-----------------------
	
	/**
	 * 幸福抢先看入口
	 */
	public function sf_index(){
	    //检查用户是否登陆
	    $this->check_login();
	    $model_user=D("Users");
	    $user_id = session("user.id");
	    if($_POST){
	        try {
	            $user_phone = I("post.mobile");
	            $messcode       = I("post.messcode");
	            //检查电话号格式
	            if(empty($user_phone)) $this->error("请输入手机号");
	            if(!preg_match("/^1\d{10}$/",$user_phone)){
	                $this->error('手机号码格式不正确');
	            }
	            if(empty($messcode))   $this->error("请输入验证码");
	            //需要获取到的短信验证码验证规则
	            $verifyCode = session("XINGFUSMS");;
	            if($messcode != $verifyCode){
	                $this->error("短信验证码不正确，请重新验证！");
	            }
	             
	            $model_xf = D("Pcustom");
	            
	            //检查电话号是否在抢先看中存在
	            $hasXF=$model_xf->where("cid = 1 and (tel1='$user_phone' or tel2='$user_phone')")->order("id desc")->find();
	            if(!$hasXF)
	                $this->error("没有查找到您的抢先看，请查看手机号是否正确或联系管理员");
	            $result = $model_user->where("id = $user_id")->setField("xf_code",$hasXF['keywords']);
	            if($result){
	                $url = U("PCustom/sf_info",array("id"=>$hasXF['keywords']));
	                $this->success("绑定成功",$url);
	            }else{
	                $this->error("绑定失败，请联系管理员");
	            }
	        } catch (\Exception $e) {
	            $this->error($e->getMessage());
	        }
	         
	         
	    }else{
	        $xf_code = $model_user->where("id = $user_id")->getField("xf_code");
	        if(empty($xf_code)){
	            $this->display();
	        }else{
	            $url = U("PCustom/sf_info",array("id"=>$xf_code));
	            header("location:$url");
	            exit();
	        }
	    }
	}
	
	/**
	 * 幸福抢先看详情页
	 */
	public function sf_info(){
	    $id			= I("get.id");
	    if(empty($id)){
	        $this->error("参数丢失");
	    }
	    $where		= "keywords='$id' and status=1 and cid=1";
	    $info		= $this->model_pcustom->where($where)->find();
	    if(!$info) $this->error("无效链接");
	    if(!empty($info['post_url'])){
	        header("location:".$info['post_url']);exit;
	    }
	
	    //banner
	    $this->assign("home_head",$this->_getAd("banner_pcustom"));
	    //3个摆放在一起的二级页面广告位
	    $this->assign("second_page_3",$this->_getAd("second_page_3"));
	    //服务承诺
	    $this->assign("servePromise",$this->_getAd("servePromise"));
	    //广告位--蕴含美态
	    $this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
	    //详细内容
	    $this->assign("info",$info);
	    //图片信息
	    $photo	= json_decode($info['smeta'],true);
	    $this->assign("photo",$photo['photo']);
	    $this->display();
	}
	
	/**
	 * 发送幸福抢先看验证码
	 */
	public function sendXFSMS(){
	    try{
	        $user_phone=I("post.user_phone");
	        $model_user=D("Users");
	        $model_xf = D("Pcustom");
	        $hasXF=$model_xf->where("cid = 1 and (tel1='$user_phone' or tel2='$user_phone')")->find();
	        if(!$hasXF)$this->error("没有查找到您的抢先看，请查看手机号是否正确或联系管理员");
	        $code=rand(1000, 9999);
	        $content="您好，您的幸福抢先看查询验证码是:".$code.",如非本人操作请忽略【大连洛可可婚纱摄影】";
	        $reply=$this->authcode($user_phone, $content);
	        session("XINGFUSMS",$code);
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