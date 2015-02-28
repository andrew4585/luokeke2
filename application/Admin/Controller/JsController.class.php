<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class JsController extends AdminbaseController{
	
	
	protected $model_js;
	
	function _initialize() {
		parent::_initialize();
		$this->model_js = D("Js");
	}
	
	//列表
	public function index(){
		$list= $this->model_js->field("id,js_name,status,listorder")->order("listorder")->select();
		$this->assign("list",$list);
		$this->display();
	}	
	
	//添加
	public function add(){
		if(IS_POST){
			
		}else{
			$this->display();
		}
	}
	
	//编辑
	public function edit(){
		
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