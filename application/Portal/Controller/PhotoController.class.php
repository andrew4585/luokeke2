<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
class PhotoController extends HomeBaseController {
	
	public function nav_index(){
		$m 			= M('photo_cat');
		$msg 		= $m->where()->select();
		$item = array();
		foreach ($msg as $key=>$value){
			$item[] = array(
						"label" => "{$value['cat_name']}",
						"href" => U("Portal/Photo/cid/{$value['id']}")
					);
		}
		$nav_arr['name'] 	= "作品分类";
		$nav_arr['items'] 	= $item;
		exit(json_encode($nav_arr));
		
	}
}