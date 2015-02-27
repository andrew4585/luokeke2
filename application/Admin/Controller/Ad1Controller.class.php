<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class Ad1Controller extends AdminbaseController{
	
	protected $ad1_obj;
	protected $ad1cat_obj;
	protected $site_obj;
	
	function _initialize() {
		parent::_initialize();
		$this->ad1_obj = D("Common/Ad1");
		$this->ad1cat_obj = D("Common/Ad1Cat");
		$this->site_obj = D("Common/Site");
	}
	function index(){
		$cates=array(
				array("cid"=>"0","cat_name"=>"默认分类"),
		);
    	$categorys=$this->ad1cat_obj->field("cid,cat_name")->where("cat_status!=0")->select();
		$list=$this->site_obj->field("id,site_name")->where("cate_id!=2")->select();
//		echo $this->site_obj->getlastsql();
//		echo $this->ad1cat_obj->getlastsql();
		if($categorys){
			$categorys=array_merge($cates,$categorys);
		}else{
			$categorys=$cates;
		}
	   $this->assign("categorys",$categorys);
		$where="";
		$id=0;
		if(isset($_POST['cid']) && $_POST['cid']!=""){
			$cid=$_POST['cid'];
			$where="ad_cid=$cid";
		}
		if(isset($_POST['id']) && $_POST['id']!=""){
			$id=$_POST['id'];
			$where="site_cid=$id";
		}
		$this->assign("list",$list);
    	$this->assign("ad_cid",$cid);
		$this->assign("site_cid",$id);
		$slides=$this->ad1_obj->where($where)->order("listorder ASC")->select();
		$this->assign('slides',$slides);
		$this->display();
	}
	function add(){
		//获取广告的分类
		$categorys=$this->ad1cat_obj->field("cid,cat_name")->where("cat_status!=0")->select();
		//获取分站名称
		$list=$this->site_obj->field("id,site_name")->where("cate_id!=2")->select();
		$this->assign("list",$list);
		$this->assign("categorys",$categorys);
		$this->display();
	}

	/**
	 *
     */
	function add_post(){
		if(IS_POST){
			if ($this->ad1_obj->create()) {
				$_POST['ad_pic']=sp_asset_relative_url($_POST['ad_pic']);
				/*
				 * 裁剪图片
				 */
//				import("@.ORG.Resizeimage");
//				$resize=new Resizeimage();
//				$resize->initAttribute("", 200, 200, 2);
//				$resize->setSize(200, 200);
//				$cutimg=$resize->resize($_POST['ad_pic']);
				if ($this->ad1_obj->add($_POST)!==false) {
					$this->success("添加成功！", U("ad1/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->ad1_obj->getError());
			}
		}
	}
	function edit(){
		$categorys=$this->ad1cat_obj->field("cid,cat_name")->where("cat_status!=0")->select();
		$list=$this->site_obj->field("id,site_name")->where("cate_id!=2")->select();
//         dump($list);
		$id= intval(I("get.id"));
		$slide=$this->ad1_obj->where("ad_id=$id")->find();
		$this->assign($slide);
        $this->assign("list",$list);
		$this->assign("categorys",$categorys);
		$this->display();
	}
	
	function edit_post(){
		if(IS_POST){
			if ($this->ad1_obj->create()) {
				$_POST['ad_pic']=sp_asset_relative_url($_POST['ad_pic']);
				if ($this->ad1_obj->save($_POST)!==false) {
					$this->success("保存成功！", U("ad1/index"));
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->slide_obj->getError());
			}
				
		}
	}

	function delete(){
		if(isset($_POST['ids'])){
			$ids = implode(",", $_POST['ids']);
			$data['ad_status']=0;
			if ($this->ad1_obj->where("ad_id in ($ids)")->delete()!==false) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}else{
			$id = intval(I("get.id"));
			if ($this->ad1_obj->delete($id)!==false) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		
	}
	function toggle(){
		if(isset($_POST['ids']) && $_GET["display"]){
			$ids = implode(",", $_POST['ids']);
			$data['ad_status']=1;
			if ($this->ad1_obj->where("ad_id in ($ids)")->save($data)!==false) {
				$this->success("显示成功！");
			} else {
				$this->error("显示失败！");
			}
		}
		if(isset($_POST['ids']) && $_GET["hide"]){
			$ids = implode(",", $_POST['ids']);
			$data['ad_status']=0;
			if ($this->ad1_obj->where("ad_id in ($ids)")->save($data)!==false) {
				$this->success("隐藏成功！");
			} else {
				$this->error("隐藏失败！");
			}
		}
	}
	
	//排序
	public function listorders() {
		$status = parent::_listorders($this->ad1_obj);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	
}