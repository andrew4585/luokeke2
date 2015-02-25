<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
/**
 * 文章列表
*/
class ListController extends HomeBaseController {

	//文章内页
	public function index() {
		$term=sp_get_term($_GET['id']);
		$tplname=$term["list_tpl"];
    	$tplname=sp_get_apphome_tpl($tplname, "list");
    	$this->assign($term);
    	$this->assign('cat_id', intval($_GET['id']));
    	$this->display(":$tplname");
	}
	
	public function nav_index(){
		
		$nav_arr = array(
				"name"	=> "a",
				"items" => array(
							0 =>array(	"label" => "a1",
										"href"	=> "http://localhost/luokeke2/"
									),
							),
				
				
				);
		exit(json_encode($nav_arr));
	}
	
}
