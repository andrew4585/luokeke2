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
			header("location:".$info['post_url']);exit;
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
		$this->assign("model_table","PCustom");
		$this->display();
	}
	public function lists(){
		$this->_list($this->model_pcustom,true,array(),array(),16,"recommended desc,listorder");
		$this->assign("ad_dress",$this->_getAd("dress"));
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		//服务承诺
		$this->assign("servePromise",$this->_getAd("servePromise"));
		$this->display("list");
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