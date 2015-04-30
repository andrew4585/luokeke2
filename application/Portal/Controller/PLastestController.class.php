<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
class PLastestController extends IndexController {

	public function nav_index(){
		$nav_arr['name'] 	= "最新推荐客片";
		$nav_arr['name_url'] = U("Portal/PLastest/index");
		exit(json_encode($nav_arr));
	}
	
	public function index(){
		$where		= "recommended=1 and status=1 and site_id={$this->siteId}";
		$model_lastest = D('plastest');
		$info		= $model_lastest->where($where)->limit(0,1)->find();
		//banner
		$this->assign("home_head",$this->_getAd("banner_pcustom"));
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		$this->assign("servePromise",$this->_getAd("servePromise"));
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		$this->assign("info",$info);
		$smeta = json_decode($info['smeta'],true);
		$this->assign("smeta",$smeta['photo']);
		$this->display('/PCustom/new');
	}
}