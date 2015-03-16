<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
class DressController extends HomeBaseController {

	public function nav_index(){
		$m 			= M('dress_cat');
		$msg 		= $m->where()->select();
		$item = array();
		foreach ($msg as $key=>$value){
			$item[] = array(
					"label" => "{$value['cat_name']}",
					"href" => "Portal/Dress/list/{$value['id']}"
							);
		}
		$nav_arr['name'] 	= "婚纱礼服分类";
		$nav_arr['items'] 	= $item;
		exit(json_encode($nav_arr));
	}
}