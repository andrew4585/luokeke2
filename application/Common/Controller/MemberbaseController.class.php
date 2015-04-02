<?php
namespace Common\Controller;
use Portal\Controller\IndexController;
class MemberbaseController extends IndexController{
	
	function _initialize() {
		parent::_initialize();
		
		$this->check_login();
		//$this->check_user();
	}
	
}