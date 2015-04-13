<?php

namespace Portal\Controller;

class DressController extends IndexController {

	protected $category 	= array(1=>'婚纱',2=>'礼服');
	protected $model_dress ;
	
	public function __construct(){
		parent::__construct();
		$this->model_dress=D("Dress");
		$this->assign('bannerpic',1);
	}
	/**
	 * 首页
	 */
	public function index(){
		
		//最新广告推荐
		$Ad = D('Ad1');
		$Ad_five = $Ad->where('ad_cid=18')->order('listorder')->select();
		$this->assign('Ad_five',$Ad_five);
		//绝美礼服
		$beauty		=$this->model_dress->where("cid=8")->order("recommended desc,listorder")->limit(0,20)->select();
		$this->assign("beauty",$beauty);
		//dump($beauty);
		//最新婚纱定制
		$fieldArr	= array("id","post_pic","post_title","post_url");
		//status：是否显示
		$whereArr	= array("status=1","category=1");
		$where		= join(" and ",$whereArr);
		$field		= join(",",$fieldArr);
		$dress		= $this->model_dress->field($field)->where($where)->order("recommended desc,listorder")->limit(0,20)->select();
		$this->assign("dress",$dress);
		//广告位显示
		$this->assign("promise",$this->_getAd("promise"));
		$this->assign("servePromise",$this->_getAd("servePromise"));
		$this->display('/Dress/index');
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
		$info		= $this->model_dress->where($where)->find();
		if(!empty($info['post_url'])){
			header("location:".$info['post_url']);
		}
		//banner
		$this->assign("home_head",$this->_getAd("banner_dress"));
		//婚纱礼服广告位（专用）
		$this->assign("ad_dress",$this->_getAd("dress"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		//服务承诺
		$this->assign("servePromise",$this->_getAd("servePromise"));
		//详细内容
		$this->assign("info",$info);
		//评论信息
		$this->getCommentList("Dress",$info['id']);
		$this->assign("table",'Dress');
		//获取本页面的url
		$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$this->assign("url",$url);
		//图片信息
		$photo	= json_decode($info['smeta'],true);
		$this->assign("photo",$photo['photo']);
		//二维码
		$this->qrcode();
		//右侧导航
		$this->info_right();
		$this->assign("model_table","Dress");
		$this->display();
	}
	/**
	 *列表页
	 */
	public function lists(){
		$category = I("get.category",0,'intval');
		if(empty($category)){
			$this->error("参数丢失");
		}
		$this->_list($this->model_dress,false,array(),array("category={$category}"));
		$this->assign("ad_dress",$this->_getAd("dress"));
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		//服务承诺
		$this->assign("servePromise",$this->_getAd("servePromise"));
		//婚纱礼服广告位（专用）
		$this->assign("ad_dress",$this->_getAd("dress"));
		
		$this->display("list");
	}
	
	/**
	 * 用户点击‘喜欢’按钮
	 */
	public function ajax_like(){
		$this->_like($this->model_dress);
	}
	
	public function nav_index(){
		$m 			= M('dress_cat');
		$msg 		= $m->where()->select();
		$item = array();
		foreach ($msg as $key=>$value){
			$item[] = array(
					"label" => "{$value['cat_name']}",
					"href" => U("Portal/Dress/lists/category/{$value['id']}")
					
							);
		}
		$nav_arr['name'] 	= "婚纱礼服分类";
		$nav_arr['items'] 	= $item;
		exit(json_encode($nav_arr));
	}
}