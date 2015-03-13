<?php
namespace Content\Controller;
/**
 * 婚纱管理
 * @author duostec
 */
class DressFormalController extends DressController {
	
	
	function _initialize() {
		parent::_initialize();
		$this->cate_id=2;
		$this->assign("controlName","DressFormal");
	}
	
	function index(){
		$this->_index();
	}
	
	function add(){
		$this->_add();
	}
	
	public function edit(){
		$this->_edit();
	}
	
}