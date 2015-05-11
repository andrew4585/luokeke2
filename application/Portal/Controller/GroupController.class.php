<?php
namespace Portal\Controller;
class GroupController extends IndexController {

	protected $model_group ;
	
	public function __construct(){
		parent::__construct();
		$this->model_group=D("Group");
	}
	
	public function lists(){
		$group = $this->getHomeContent("Group",true,array("post_price","post_num"));
		$this->assign("group",$group);
		$this->display("list");
	}

	public function info(){
		$id			= I("get.id",0,'intval');
		$where		= "site_id=$this->siteId and status=1";
		$whereInfo	= empty($id)?$where:"id=$id and ".$where;
		
		$order		= "listorder,post_start desc";
		$info		= $this->model_group->where($whereInfo)->order($order)->find();
		if(!$info){
			$this->error("非法操作");
		}
		$id			= $info['id'];
		if(!empty($info['post_url'])){
			header("location:".$info['post_url']);exit;
		}
		//服务流程图片
		$this->assign("serviceProcess",$this->_getSingle(29));
		//付款方式图片
		$this->assign("payMoney",$this->_getSingle(30));
		//右侧信息
		$rightList	= $this->model_group->field("id,post_pic,post_title,post_price")->where($where)->order($order)->select();
		$this->assign("rightList",$rightList);
		//banner
		$this->assign("home_head",$this->_getAd("banner_group"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		//服务承诺
		$this->assign("servePromise",$this->_getAd("servePromise"));
		//详细内容
		$this->assign("info",$info);
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
		$this->assign("model_table","Group");
		$this->display();
	}
	
	/**
	 * 用户点击‘喜欢’按钮
	 */
	public function ajax_like(){
		$this->_like($this->model_group);
	}
	
	public function nav_index(){
		exit();
		$m 			= M('group_cat');
		$msg 		= $m->where()->select();
		$item 		= array();
		foreach ($msg as $key=>$value){
			$item[] = array(
						"label" => "{$value['cat_name']}",
						"href" => U("Portal/Group/lists/cid/{$value['id']}")
					);
		}
		$nav_arr['name'] 	= "团购分类";
		$nav_arr['items'] 	= $item;
		exit(json_encode($nav_arr));
	}
}