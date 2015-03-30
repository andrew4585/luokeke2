<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
class PLastestController extends IndexController {
	
	public function nav_index(){
		$m 			= M('plastest');
		$msg 		= $m->where('status=1')->find();
		$nav_arr = array(
				"name"	=> "最新推荐客片",
				"items" => array(
						0 =>array(	"label" => "{$msg['post_title']}",
									"href" => U("Portal/PLastest/index/id/{$msg['id']}")
						),
				),
		
		
		);
		exit(json_encode($nav_arr));
	}
	
	public function index(){
		$where		= "recommended=1 and status=1";
		$model_lastest = D('plastest');
		$info		= $model_lastest->where($where)->limit(0,1)->find();
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		$this->assign("servePromise",$this->_getAd("servePromise"));
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		$this->assign("info",$info);
		$smeta = json_decode($info['smeta'],true);
		$this->assign("smeta",$smeta['photo']);
		$this->display('/PCustom/new');
	}
}