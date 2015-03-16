<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
class GroupController extends HomeBaseController {

	public function nav_index(){
		$m 			= M('group_cat');
		$msg 		= $m->where()->select();
		$item = array();
		foreach ($msg as $key=>$value){
			$item[] = array(
					"label" => "{$value['cat_name']}",
					"href" => "Portal/Group/list/{$value['id']}"
							);
		}
		$nav_arr['name'] 	= "团购分类";
		$nav_arr['items'] 	= $item;
		exit(json_encode($nav_arr));
	}
}