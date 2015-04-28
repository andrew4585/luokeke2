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
		$info = $shareModel->where("id=$id")->find();
		$picUrl = $info['post_pic'];
		$url = $this->_getUri();
		$this->qrcode();
		$this->assign('picurl',$picUrl);
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
		if(empty($url)){
			$this->assign("qrcode",$path);
		}else{
			return $path;
		}
	}
}
