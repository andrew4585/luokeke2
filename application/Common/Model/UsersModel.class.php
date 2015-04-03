<?php
namespace Common\Model;
use Common\Model\CommonModel;
class UsersModel extends CommonModel
{
	
	protected $_validate = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
			array('user_login', 'require', '用户名称不能为空！', 0, 'regex', CommonModel:: MODEL_BOTH ),
			array('user_login','','用户名已经存在！',0,'unique',CommonModel:: MODEL_BOTH ), // 验证user_login字段是否唯一
			array('user_phone','','该手机号已经存在！',0,'unique',CommonModel:: MODEL_BOTH ), // 验证user_phone字段是否唯一
			array('user_phone','/^(1(([35][0-9])|(47)|[8][0126789]))\d{8}$/','手机格式不正确！',0,'',CommonModel:: MODEL_BOTH ), // 验证user_phone字段格式是否正确
	);
	
	//用于获取时间，格式为2012-02-03 12:12:12,注意,方法不能为private
	function mGetDate() {
		return date('Y-m-d H:i:s');
	}
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
		
		if(!empty($data['user_pass']) && strlen($data['user_pass'])<25){
			$data['user_pass']=sp_password($data['user_pass']);
		}
	}
	
}

