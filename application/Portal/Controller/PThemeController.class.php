<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
class PThemeController extends IndexController {
	protected $model_PTheme	;

	public function __construct(){
		parent::__construct();
		$this->model_PTheme	= D("Ptheme");
	}

	function lists(){
		$this->_list($this->model_PTheme,true,array(),array(),16,"recommended desc,listorder");
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		//服务承诺
		$this->assign("servePromise",$this->_getAd("servePromise"));
		$this->display("list");
	}

	public function nav_index(){
		$m 			= M('ptheme');
		$msg 		= $m->where()->select();
		$item 		= array();
		foreach ($msg as $key=>$value){
			$item[] = array(
						"label" => "{$value['post_title']}",
						"href" => U("Portal/PTheme/lists/cid/{$value['id']}")
						
					);
		}
		$nav_arr['name'] 	= "主题作品";
		$nav_arr['items'] 	= $item;
		exit(json_encode($nav_arr));
		
	}
}