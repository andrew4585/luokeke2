<?php
namespace Common\Model;
use Think\Model\RelationModel;

class ActiveModel extends RelationModel
{
	protected $_link = array(
			'Site' => array(
					'mapping_type'  => self::BELONGS_TO,
					'class_name'    => 'Site',
					'foreign_key'   => 'site_id',
					'mapping_name'  => 'site',
			),
	);
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
	
}

