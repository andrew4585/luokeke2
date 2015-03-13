<?php
namespace Content\Controller;
/**
 * 婚纱管理
 * @author duostec
 */
class DressWeddingController extends DressController {
	
	
	function _initialize() {
		parent::_initialize();
		$this->cate_id=1;
		$this->assign("controlName","DressWedding");
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