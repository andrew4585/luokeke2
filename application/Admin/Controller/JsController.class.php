<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class JsController extends AdminbaseController{
	
	
	protected $model_js;
	
	function _initialize() {
		parent::_initialize();
		$this->model_js = D("Common/Js");
	}
	
	//列表
	public function index(){
		$count	= $this->model_js->count();
		$page	= $this->page($count,20);
		$list	= $this->model_js->field("id,js_name,desc,status,listorder")
								 ->order("listorder")
								 ->limit($page->firstRow,$page->listRows)->select();
		$this->assign("list",$list);
		$this->assign("page", $page->show('Admin'));
		$this->display();
	}	
	
	//添加
	public function add(){
		if(IS_POST){
			if ($this->model_js->create()) {
				$result=$this->model_js->add($_POST);
				if ($result!==false) {
					$this->success("添加成功！", U("Js/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->model_js->getError());
			}
		}else{
			$this->display();
		}
	}
	
	//编辑
	public function edit(){
		
		$js_id = $_POST['id']?$_POST['id']:$_GET['id'];
		
		if(empty($js_id)) $this->error("站点编号丢失");
			
		if(IS_POST){
			if($this->model_js->create()){
				if ($this->model_js->save($_POST)!==false) {
					$this->success("修改成功！", U("Js/edit","id=$js_id"));
				} else {
					$this->error("修改失败！");
				}
			}else{
				$this->error($this->model_js->getError());
			}
		}else{
			$info = $this->model_js->where("id=$js_id")->find();
			if($info){
				$this->assign("info",$info);
				$this->display();
			}else{
				$this->error("应用不存在或数据获取失败");
			}
		}
	}
	
	//删除
	public function delete(){
		
		
	}
	
	//排序
	public function listorders() {
		$status = parent::_listorders($this->model_js);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	
	//修改状态
	public function status(){
		$status = parent::_status($this->model_js);
		if ($status) {
			echo "success";
		} else {
			echo "fail";
		}
	}
}