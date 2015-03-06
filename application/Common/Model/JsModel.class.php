<?php
namespace Common\Model;
use Common\Model\CommonModel;
class JsModel extends CommonModel{
	
	//自动验证
	protected $_validate = array(
			array('js_name', 'require', '应用名称不能为空！', 1, 'regex', 3),
			array('content', 'require', '应用内容不能为空！', 1, 'regex', 3),
	);
	
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
	
}




