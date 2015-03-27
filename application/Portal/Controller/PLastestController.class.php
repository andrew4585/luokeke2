<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
class PLastestController extends HomeBaseController {
	
	public function nav_index(){
		$m 			= M('plastest');
		$msg 		= $m->where('status=1')->find();
		$nav_arr = array(
				"name"	=> "最新推荐客片",
				"items" => array(
						0 =>array(	"label" => "{$msg['post_title']}",
									"href" => U("Portal/PLastest/lists/cid/{$msg['id']}")
						),
				),
		
		
		);
		exit(json_encode($nav_arr));
	}
}