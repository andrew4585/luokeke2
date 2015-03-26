<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
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
		$where		= "id=$id and status=1";
		$info		= $this->model_pcustom->where($where)->find();
		if(!empty($info['post_url'])){
			header("location:".$info['post_url']);
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
	
	
	public function nav_index(){
	}
}