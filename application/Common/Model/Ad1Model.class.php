<?php
namespace Common\Model;
use Think\Model\RelationModel;

class Ad1Model extends RelationModel{
	
	protected $_link = array(         
			'Site' => array(    
					'mapping_type'  => self::BELONGS_TO,    
					'class_name'    => 'Site',    
					'foreign_key'   => 'site_cid',    
					'mapping_name'  => 'site',    
					),
			'Ad1Cat' => array(
					'mapping_type'  => self::BELONGS_TO,
					'class_name'    => 'Ad1Cat',
					'foreign_key'   => 'ad_cid',
					'mapping_name'  => 'cat',
			),
	);
	
	//自动验证
	protected $_validate = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
			array('ad_name', 'require', '广告名称不能为空！', 1, 'regex', 3),

	);
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
}