<?php
namespace Common\Model;
use Think\Model\RelationModel;

class PhotoModel extends RelationModel
{
	protected $_link = array(
			'Site' => array(
					'mapping_type'  => self::BELONGS_TO,
					'class_name'    => 'Site',
					'foreign_key'   => 'site_id',
					'mapping_name'  => 'site',
			),
			'PhotoCat' => array(
					'mapping_type'  => self::BELONGS_TO,
					'class_name'    => 'PhotoCat',
					'foreign_key'   => 'cid',
					'mapping_name'  => 'cat',
			),
	);
	
	
	protected $_validate = array(
			array('post_title', 'require', '作品标题不能为空！', 1, 'regex', CommonModel::MODEL_BOTH  ),
	);
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
	
}

