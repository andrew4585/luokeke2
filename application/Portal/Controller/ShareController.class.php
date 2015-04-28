<?php
namespace Portal\Controller;

use Think\Log;
use Common\Controller\HomeBaseController;

class ShareController extends HomeBaseController {
	

	public function __construct() {
		parent::__construct();
		$this->check_login();
	}
	/**
	 * 分享页面
	 */
	function shareWindow($model,$id=0){

		$shareModel = D($model);
		if($model == "Article"){
			$url = 'http://'.$_SERVER['HTTP_HOST'].U("Portal/Article/info")."/id/".$id;
		}else{
			$url = $this->_getUri();
		}
		$info = $shareModel->where("id=$id")->find();
		$picUrl = $info['post_pic'];
		$this->qrcode($url);
		$this->assign('picurl',$picUrl);
		$this->assign('avatar',$_SESSION['user']['avatar']);
		$this->display();
	}
	
    /**
     * 获取frame所在页面的网址 就是前一个页面
     */
    public function _getUri(){
    	$url = $_SERVER['HTTP_REFERER'];
    	return $url;
    }


	/**
	 * 生成二维码
	 */
	public function qrcode($url=''){
		if(empty($url)){
			$uri = $this->_getUri();
		}else{
			$uri = $url;
		}
		$name	= encrypt($uri);
		$path	= "./data/upload/qrcode/$name.png";
		if(!file_exists($path)){
			Vendor("phpqrcode.phpqrcode");
			\QRcode::png($uri,$path,'L',1000,2);
		}
		return $this->assign("qrcode",$path);
	}
}
