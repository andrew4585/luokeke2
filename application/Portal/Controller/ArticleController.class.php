<?php
namespace Portal\Controller;

class ArticleController extends IndexController {
	
	public function nav_index() {
		$m 			= M('article_cat');
		$msg 		= $m->where()->select();
		$item 		= array();
		foreach ($msg as $key=>$value){
			$item[] = array(
						"label" => "{$value['cat_name']}",
						"href" => U("Portal/Article/lists/cid/{$value['id']}")
					);
		}
		$nav_arr['name'] 	= "婚嫁分类";
		$nav_arr['items'] 	= $item;
		exit(json_encode($nav_arr));
	}
	public function lists() {
		//美态
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		$this->assign("servePromise",$this->_getAd("servePromise"));
		$this->display('list');
	}

	public function info(){
		//美态
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		$this->assign("servePromise",$this->_getAd("servePromise"));
		$this->display('info');
	}
}