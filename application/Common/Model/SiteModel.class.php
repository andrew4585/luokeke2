<?php
namespace Common\Model;
use Common\Model\CommonModel;
class SiteModel extends CommonModel
{
	
	public $category = array(1=>"普通",2=>"url链接");
	
	protected $_validate = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
			array('site_name', 'require', '站点不能为空！', 1, 'regex', CommonModel::MODEL_BOTH  ),
			array('cate_id', 'require', '站点分类为空！', 1, 'regex', CommonModel:: MODEL_BOTH ),
	);
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
	
}

