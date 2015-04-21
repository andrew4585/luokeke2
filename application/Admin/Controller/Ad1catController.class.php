<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class Ad1catController extends AdminbaseController{
	
	protected $ad1cat_obj;
	
	function _initialize() {
		parent::_initialize();
		$this->ad1cat_obj = D("Common/Ad1Cat");
	}
	
	function index(){
		$cats=$this->ad1cat_obj->where("cat_status!=0")->order("cid")->select();
		$this->assign("ad1cat",$cats);
		$this->display();
	}
	
 	/**
     *  添加
     */
    public function add() {
        $this->display();
    }
	
    /**
     *  添加
     */
    public function add_post() {
    	if (IS_POST) {
    		if ($this->ad1cat_obj->create()) {
    			if ($this->ad1cat_obj->add()!==false) {
    				$this->success("添加成功！", U("ad1cat/index"));
    			} else {
    				$this->error("添加失败！");
    			}
    		} else {
    			$this->error($this->ad1cat_obj->getError());
    		}
    	}
    }
	function edit(){
		$id= intval(I("get.id"));
		$ad1cat=$this->ad1cat_obj->where("cid=$id")->find();
		$this->assign($ad1cat);
		$this->display();
	}
	
	function edit_post(){
		if (IS_POST) {
			if ($this->ad1cat_obj->create()) {
				if ($this->ad1cat_obj->save()!==false) {
					$this->success("保存成功！", U("ad1cat/index"));
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->ad1cat_obj->getError());
			}
		}
	}
	
	
	function delete(){

		$id = intval(I("get.id"));
		if ($this->ad1cat_obj->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
		
	}
	
	
}