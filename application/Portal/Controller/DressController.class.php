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
		//图片信息
		$photo	= json_decode($info['smeta'],true);
		$this->assign("photo",$photo['photo']);
		//右侧导航
		$this->info_right();
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
		import('Page');// 导入分页类		
		//status=1,表示文章未删除，0表示文章已删除
		$where_ands =array("status=1","category={$category}");
		$order		="listorder ASC,post_date DESC";
		$where= join(" and ", $where_ands);	
		
		$count=$this->model_dress->where($where)->count();
		//下面分页显示
		$Page       = new \Page($count,1);
		$Page->SetPager('Home', '{first}{prev}&nbsp;{liststart}{list}{listend}&nbsp;{next}{last}', array("listlong" => "6", "first" => "首页", "last" => "尾页", "prev" => " < ", "next" => " > ", "list" => "*", "disabledclass" => ""));
		$list = $this->model_dress->where($where)->order($order)->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);// 赋值数据集
		$this->assign('page',$Page->show("Home"));// 赋值分页输出
		$this->assign("ad_dress",$this->_getAd("dress"));
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		//4个摆放在一起的二级页面广告位
		$this->assign("second_page_4",$this->_getAd("second_page_4"));
		//服务承诺
		$this->assign("servePromise",$this->_getAd("servePromise"));
		//婚纱礼服广告位（专用）
		$this->assign("ad_dress",$this->_getAd("dress"));
		$this->display('/Dress/list');
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