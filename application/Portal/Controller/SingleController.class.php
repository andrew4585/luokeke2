<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
class SingleController extends IndexController {
	protected $model	;

	public function __construct(){
		parent::__construct();
		$this->model	= D("Single");
	}

	function info(){
		$id = I("get.id",0,'intval');
		if(empty($id)){
			$where = "status = 1";
		}else{
			$where = "id=$id and status=1";
		}
		$type	= $this->model->where($where)->getField("cid");
		$smeta	= $this->model->where($where)->getField("smeta");
		$title  = $this->model->where($where)->getField('post_title');
		$this->assign('title',$title);
		$smeta	= json_decode($smeta,true);
		$photo	= $smeta['photo'];
		$display= '';
		if(sp_is_mobile()){
			$this->assign("photo",$photo);
			$this->display("single1");
		}else{
			if($type==1){
				$this->assign("home_head",$this->_getAd("banner_single"));
				$this->assign("photo",$photo);
				$display  = "single2";
			}elseif ($type==2){
				$newPhoto = array();
				foreach ($photo as $item){
					array_push($newPhoto, "'".__ROOT__."/{$item['url']}'");
				}
				$photoStr = join(",", $newPhoto);
				$this->assign("photo",$photoStr);
				$display  = "single1";
			}elseif ($type==3){
				$this->assign("photo",$photo);
				$display  = "single3";
			}elseif ($type==4){
			    $this->assign("photo",$photo);
			    $display  = "single4";
			}else{
				$this->error("文章出错");
			}

			$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
			//3个摆放在一起的二级页面广告位
			$this->assign("second_page_3",$this->_getAd("second_page_3"));
			//服务承诺
			$this->assign("servePromise",$this->_getAd("servePromise"));
			$this->display($display);
		}

	}

	public function nav_index(){
		$m 			= M('Single');
		$msg 		= $m->where('status=1 and recommended=1')->select();
		$item = array();
		foreach ($msg as $key=>$value){
			$item[] = array(
				"label" => "{$value['post_title']}",
				"href" => U("Portal/Single/info/id/{$value['id']}")
			);
		}
		$nav_arr['name'] 	= "单页面";
		$nav_arr['name_url'] = U("Portal/index");
		$nav_arr['items'] 	= $item;
		exit(json_encode($nav_arr));

	}

}