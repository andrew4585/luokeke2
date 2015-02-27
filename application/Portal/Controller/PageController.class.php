<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
class PageController extends HomeBaseController{
	public function index() {
		$id=$_GET['id'];
		$content=sp_sql_page($id);
		$this->assign($content);
		$smeta=json_decode($content['smeta'],true);
		$tplname=isset($smeta['template'])?$smeta['template']:"";
		
		$tplname=sp_get_apphome_tpl($tplname, "page");
		
		$this->display(":$tplname");
	}
	
	public function nav_index(){
		$nav_arr = array(
				"name"	=> "b",
				"items" => array(
							0 =>array(	"label" => "b1",
										"href"	=> "http://localhost/luokeke2/2"
									),
							),
				
				
				);
		exit(json_encode($nav_arr));
	}
}