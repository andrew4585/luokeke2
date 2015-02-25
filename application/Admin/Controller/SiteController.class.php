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
		
		$list = $this->model_site->where($where)->select();
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
	
	
	function add_post(){
		if(IS_POST){
			if ($this->slide_obj->create()) {
				$_POST['slide_pic']=sp_asset_relative_url($_POST['slide_pic']);
				if ($this->slide_obj->add($_POST)!==false) {
					$this->success("添加成功！", U("slide/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->slide_obj->getError());
			}
		}
	}
	function edit(){
		$categorys=$this->slidecat_obj->field("cid,cat_name")->where("cat_status!=0")->select();
		$id= intval(I("get.id"));
		$slide=$this->slide_obj->where("slide_id=$id")->find();
		$this->assign($slide);
		$this->assign("categorys",$categorys);
		$this->display();
	}
	/**
	 * 数据操作（添加、修改）
	 * @param int $id	站点编号
	 */
	private function modify($id){
		$data['site_name']	= I("post.site_name");
		$data['site_url']	= I("post.site_url");
		$data['cate_id']	= I("post.cate_id");
		try {
			if(empty($id)){
				$result=$this->model_site->data($data)->add();
			}else{
				$data['id'] = $id;
				$result = $this->model_site->save($data);
			}
			if($result===false){
				
			}
			return $result;
		} catch (\Exception $e) {
			$this->err_msg = $e->getMessage();
			return false;
		}
	}
	
	function edit_post(){
		if(IS_POST){
			if ($this->slide_obj->create()) {
				$_POST['slide_pic']=sp_asset_relative_url($_POST['slide_pic']);
				if ($this->slide_obj->save($_POST)!==false) {
					$this->success("保存成功！", U("slide/index"));
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
			$data['slide_status']=0;
			if ($this->slide_obj->where("slide_id in ($ids)")->delete()!==false) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}else{
			$id = intval(I("get.id"));
			if ($this->slide_obj->delete($id)!==false) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		
	}
	
	function toggle(){
		if(isset($_POST['ids']) && $_GET["display"]){
			$ids = implode(",", $_POST['ids']);
			$data['slide_status']=1;
			if ($this->slide_obj->where("slide_id in ($ids)")->save($data)!==false) {
				$this->success("显示成功！");
			} else {
				$this->error("显示失败！");
			}
		}
		if(isset($_POST['ids']) && $_GET["hide"]){
			$ids = implode(",", $_POST['ids']);
			$data['slide_status']=0;
			if ($this->slide_obj->where("slide_id in ($ids)")->save($data)!==false) {
				$this->success("隐藏成功！");
			} else {
				$this->error("隐藏失败！");
			}
		}
	}
	
	//排序
	public function listorders() {
		$status = parent::_listorders($this->slide_obj);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	
}