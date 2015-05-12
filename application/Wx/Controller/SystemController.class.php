<?php
/**
 * 微信系统信息
 */
namespace Wx\Controller;
class SystemController extends IndexController {
	
	function _initialize() {
		parent::_initialize();
		if(!$this->model_config)
			$this->model_config = D("WxConfig");
	}
	
	/**
	 * 账号信息页面
	 */
	function index(){
		$info = $this->model_config->getBaseInfo();
		$this->assign("info",$info);
		$this->display();
	}
	
	/**
	 * 保存公众账号账号信息
	 */
	public function modifyBaseInfo(){
		$this->model_config->modify($_POST);
		$this->success("保存成功",U("System/index"));
	}
	
	//-------------------------------微信导航
	/**
	 * 显示菜单页面
	 */
	public function menu(){
		$model_menu = D("WxMenu");
		$list = $model_menu->getChildMenu();
		$this->assign("list",$list);
		$this->display();
	}
	/**
	 * 添加导航
	 */
	public function menuAdd(){
		$model_menu = D("WxMenu");
		if(IS_POST){
			$this->error(1);
		}else{
			$first = $model_menu->where("fid = 0")->select();
			$this->assign("first",$first);
			$this->display();
		}
	}
	
	public function menuEdit(){
		if(IS_POST){
			
		}else{
			$this->display();
		}
	}
	/**
	 * 排序
	 */
	public function listorders(){
		$model = D("WxMenu");
		$status = parent::_listorders($model);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
}

