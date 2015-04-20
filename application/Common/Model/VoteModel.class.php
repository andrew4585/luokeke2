<?php
namespace Common\Model;
use Think\Model\RelationModel;

class VoteModel extends RelationModel{

    protected $_link = array(
        'User' => array(
            'mapping_type'  => self::BELONGS_TO,
            'class_name'    => 'Users',
            'foreign_key'   => 'userid',
            'mapping_name'  => 'user',
        ),
    );



    protected function _before_write(&$data) {
        parent::_before_write($data);
    }

}