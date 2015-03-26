<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
class PhotoController extends IndexController {
	
	protected $model_photo	;
	protected $model_cat	;
	
	public function __construct(){
		parent::__construct();
		$this->model_photo	= D("Photo");
		$this->model_cat	= D("PhotoCat");
	}
	
	/**
	 * 详细页
	 */
	public function info(){
		$id			= I("get.id",0,'intval');
		if(empty($id)){
			$this->error("参数丢失");
		}
		$where		= "id=$id and status=1";
		$info		= $this->model_photo->where($where)->find();
		if(!empty($info['post_url'])){
			header("location:".$info['post_url']);
		}
		//banner
		$this->assign("home_head",$this->_getAd("banner_photo"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		//服务承诺
		$this->assign("servePromise",$this->_getAd("servePromise"));
		//详细内容
		$this->assign("info",$info);
		//图片信息
		$photo	= json_decode($info['smeta'],true);
		$this->assign("photo",$photo['photo']);
		//右侧导航
		$this->info_right();
	
		$this->display();
	}
	
	/**
	 * 获取分类信息
	 */
	public function getCategory(){
		$catList	= $this->model_cat->order("listorder")->getField("id",true);
		$this->assign("cateList",$catList);
	}
	
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