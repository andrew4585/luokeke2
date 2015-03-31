<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
class PCustomController extends IndexController {
	
	protected $model_pcustom	;
	protected $model_cat	;
	
	public function __construct(){
		parent::__construct();
		$this->model_pcustom	= D("Pcustom");
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
		$info		= $this->model_pcustom->where($where)->find();
		if(!empty($info['post_url'])){
			header("location:".$info['post_url']);
		}
		//上一组
		$prev	 	= $this->model_pcustom->where("id>$id and status=1")->order("id asc")->getField("id");
		//下一组
		$next		= $this->model_pcustom->where("id<$id and status=1")->order("id DESC")->getField("id");
		$this->assign("prev",$prev);
		$this->assign("next",$next);
		
		//banner
		$this->assign("home_head",$this->_getAd("banner_pcustom"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		//服务承诺
		$this->assign("servePromise",$this->_getAd("servePromise"));
		//广告位--蕴含美态
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		//详细内容
		$this->assign("info",$info);
		//评论信息
		$this->getCommentList("Pcustom",$info['id']);
		$this->assign("table",'Pcustom');
		//图片信息
		$photo	= json_decode($info['smeta'],true);
		$this->assign("photo",$photo['photo']);
		//获取本页面的url
		$this->assign("url",$this->_getUri());
		//生成二维码
		$this->qrcode();
		$this->display();
	}
	public function lists(){
		import('Page');// 导入分页类
		//status=1,表示文章未删除，0表示文章已删除
		$where_ands =array("status=1");
		$order		="listorder ASC,post_date DESC";
		$where= join(" and ", $where_ands);
		
		$count=$this->model_pcustom->where($where)->count();
		//下面分页显示
		$Page       = new \Page($count,2);
		$Page->SetPager('Home', '{first}{prev}&nbsp;{liststart}{list}{listend}&nbsp;{next}{last}', array("listlong" => "6", "first" => "首页", "last" => "尾页", "prev" => " < ", "next" => " > ", "list" => "*", "disabledclass" => ""));
		$list = $this->model_pcustom->where($where)->order($order)->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);// 赋值数据集
		$this->assign('page',$Page->show("Home"));// 赋值分页输出
		$this->assign("ad_dress",$this->_getAd("dress"));
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		//服务承诺
		$this->assign("servePromise",$this->_getAd("servePromise"));
		$this->display('/PCustom/list');
	}
	
	/**
	 * 用户点击‘喜欢’按钮
	 */
	public function ajax_like(){
		$this->_like($this->model_pcustom);
	}
	
	public function nav_index(){
	}
}