<?php
namespace Common\Model;
use Think\Model\RelationModel;

class PlastestModel extends RelationModel
{
	protected $_link = array(
			'Site' => array(
					'mapping_type'  => self::BELONGS_TO,
					'class_name'    => 'Site',
					'foreign_key'   => 'site_id',
					'mapping_name'  => 'site',
			),
	);
	
	
	protected $_validate = array(
			array('post_title', 'require', '标题不能为空！', 1, 'regex', CommonModel::MODEL_BOTH  ),
	);
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
	
}

