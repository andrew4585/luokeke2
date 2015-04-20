<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
class PThemeController extends IndexController {
	protected $model_PTheme	;

	public function __construct(){
		parent::__construct();
		$this->model_PTheme	= D("Ptheme");
	}

	function info(){
		$id = I("get.id",0,'intval');
		if(empty($id)){
			$where = "status = 1";
		}else{
			$where = "id=$id and status=1";
		}
		$info	= $this->model_PTheme->field("post_url,smeta")->where($where)->find();
		if(!empty($info['post_url'])){
			header("location:".$info['post_url']);exit;
		}
		$smeta	= json_decode($info['smeta'],true);
		$photo	= $smeta['photo'];
		
		$newPhoto = array();
		foreach ($photo as $item){
			array_push($newPhoto, "'".__ROOT__."/{$item['url']}'");
		}
		$photoStr = join(",", $newPhoto);
		$this->assign("photo",$photoStr);
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		//banner
		$this->assign("home_head",$this->_getAd("banner_pcustom"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		//服务承诺
		$this->assign("servePromise",$this->_getAd("servePromise"));
		$this->display(":Single:single1");
	}
	
	function lists(){
		$this->_list($this->model_PTheme,true,array(),array(),16,"recommended desc,listorder");
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		//banner
		$this->assign("home_head",$this->_getAd("banner_photo"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		//服务承诺
		$this->assign("servePromise",$this->_getAd("servePromise"));
		$this->display("list");
	}

	public function nav_index(){
		$nav_arr['name'] 	= "主题作品";
		$nav_arr['name_url'] = U("Portal/PTheme/lists");
		exit(json_encode($nav_arr));
		
	}
}