<?php
namespace Portal\Controller;
class DressController extends IndexController {

	protected $category 	= array(1=>'婚纱',2=>'礼服');
	protected $model_dress ;
	
	public function __construct(){
		parent::__construct();
		$this->model_dress=D("Dress");
	}
	
	/**
	 * 详细页
	 */
	public function info(){
		$id			= I("get.id",0,'intval');
		$category	= I("get.category",0,'intval');
		if(empty($id)&&empty($cid)){
			$this->error("参数丢失");
		}
		$where		= "id=$id and status=1";
		$info		= $this->model_dress->where($where)->find();
		if(!empty($info['post_url'])){
			header("location:".$info['post_url']);
		}
		//banner
		$this->assign("home_head",$this->_getAd("home_head"));
		//婚纱礼服广告位（专用）
		$this->assign("ad_dress",$this->_getAd("dress"));
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
	 * 右侧导航作品
	 */
	public function info_right(){
		$model_photo	= D("Photo");
		$model_pCate	= D("PhotoCat");
		$cateList		= $model_pCate->field("id,cat_name")->order("listorder")->select();
		$right			= array();
		$where			= "recommended=1 and site_id={$this->siteId} and status=1";
		foreach($cateList as $item){
			$whereAlias	    = $where." and cid=".$item['id'];
			$item['list']	= $model_photo	->field("id,post_title,post_excerpt")
											->where($whereAlias)->order("listorder,post_date desc")
											->select();
			array_push($right, $item);
		}
		//右侧导航---广告
		$this->assign("info_right",$this->_getAd("info_right"));
		//右侧导航---作品列表
		$this->assign("rightPhoto",$right);		
	}
	
	public function nav_index(){
		$m 			= M('dress_cat');
		$msg 		= $m->where()->select();
		$item = array();
		foreach ($msg as $key=>$value){
			$item[] = array(
					"label" => "{$value['cat_name']}",
					"href" => "Portal/Dress/list/category/{$value['id']}"
							);
		}
		$nav_arr['name'] 	= "婚纱礼服分类";
		$nav_arr['items'] 	= $item;
		exit(json_encode($nav_arr));
	}
}