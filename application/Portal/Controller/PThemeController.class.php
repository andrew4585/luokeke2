<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
class PThemeController extends HomeBaseController {

	public function nav_index(){
		$m 			= M('ptheme');
		$msg 		= $m->where()->select();
		$item 		= array();
		foreach ($msg as $key=>$value){
			$item[] = array(
						"label" => "{$value['post_title']}",
						"href" => U("Portal/PTheme/cid/{$value['id']}")
						
					);
		}
		$nav_arr['name'] 	= "主题作品";
		$nav_arr['items'] 	= $item;
		exit(json_encode($nav_arr));
		
	}
}