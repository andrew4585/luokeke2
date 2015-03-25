<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
class ArticleController extends HomeBaseController {
	
	public function nav_index(){
		$m 			= M('article_cat');
		$msg 		= $m->where()->select();
		$item 		= array();
		foreach ($msg as $key=>$value){
			$item[] = array(
						"label" => "{$value['cat_name']}",
						"href" => U("Portal/Article/cid/{$value['id']}")
					);
		}
		$nav_arr['name'] 	= "婚嫁分类";
		$nav_arr['items'] 	= $item;
		exit(json_encode($nav_arr));
	}
}