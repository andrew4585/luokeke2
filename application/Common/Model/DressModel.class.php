<?php
namespace Common\Model;
use Think\Model\RelationModel;

class DressModel extends RelationModel
{
	protected $_link = array(
			'DressCat' => array(
					'mapping_type'  => self::BELONGS_TO,
					'class_name'    => 'DressCat',
					'foreign_key'   => 'cid',
					'mapping_name'  => 'cat',
			),
	);
	
	
	protected $_validate = array(
			array('post_title', 'require', '标题不能为空！', 1, 'regex', CommonModel::MODEL_BOTH  ),
	);
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
	
}

