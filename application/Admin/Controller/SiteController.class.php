<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class SiteController extends AdminbaseController{
	
	protected $model_site;
	
	function _initialize() {
		parent::_initialize();
		$this->model_site = D("Common/Site");
	}
	
	function index(){
		$cid = I("post.cid");
		if(!empty($cid)){
			$where = "cate_id=$cid";
			$this->assign("cid",$cid);
		}else{
			$where = "1=1";
		}
		
		$list = $this->model_site->where($where)->order("listorder")->select();
		$this->assign("list",$list);
		$this->assign("category",$this->model_site->category);
		$this->display();
	}
	
	function add(){
		if(IS_POST){
			if($this->model_site->create()){
				if ($this->model_site->add($_POST)!==false) {
					$this->success("添加成功！", U("Site/index"));
				} else {
					$this->error("添加失败！");
				}
			}else{
				$this->error($this->model_site->getError());
			}
		}else{
			$this->assign("category",$this->model_site->category);
			$this->display();
		}
	}
	
	function edit(){
		
		$site_id = $_POST['id']?$_POST['id']:$_GET['id'];
		
		if(empty($site_id)){
			$this->error("站点编号丢失");
		}
		
		if(IS_POST){
			if($this->model_site->create()){
				if ($this->model_site->save($_POST)!==false) {
					$this->success("修改成功！", U("Site/edit","id=$site_id"));
				} else {
					$this->error("修改失败！");
				}
			}else{
				$this->error($this->model_site->getError());
			}
		}else{
			$site_info = $this->model_site->where("id=$site_id")->find();
			if($site_info){
				$this->assign("info",$site_info);
				$this->assign("category",$this->model_site->category);
				$this->display();
			}else{
				$this->error("站点不存在或数据获取失败");
			}
		}
	}
	
	
	function delete(){
		$id = intval(I("get.id"));
		if ($this->model_site->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
	
	//排序
	public function listorders() {
		$status = parent::_listorders($this->model_site);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	
}