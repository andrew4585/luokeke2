<?php
namespace Common\Model;
use Think\Model\RelationModel;

class CommentsModel extends RelationModel{
	
	protected $_link = array(
			'User' => array(
					'mapping_type'  => self::BELONGS_TO,
					'class_name'    => 'Users',
					'foreign_key'   => 'uid',
					'mapping_name'  => 'user',
			),
	);
	
	//自动验证
	protected $_validate = array(
			array('uid', 'require', '评论人编号不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH ),
			array('post_id', 'require', '内容编号不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH ),
			array('content', 'require', '评论不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH ),
			array('post_table','require','您评论的内容不存在！',1,'regex',CommonModel:: MODEL_BOTH ),
	);
	
	protected $_auto = array(
			array('createtime','mDate',1,'callback'), 
			
	);
	
	function mDate(){
		return time();
	}
	
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
	
}