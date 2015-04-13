<?php
namespace Portal\Controller;
use Think\Log;

use Common\Controller\HomeBaseController; 
class ShareController extends HomeBaseController {
	
	/**
	 * 分享页面
	 */
	function shareWindow(){
	
		$this->display();
	}
	
    /**
     * 获取当前页面网址
     */
    public function _getUri(){
    	$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    	return $url;
    }
}
