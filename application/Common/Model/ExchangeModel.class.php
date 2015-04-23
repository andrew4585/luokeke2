<?php
namespace Common\Model;

use Think\Model\RelationModel;

class ExchangeModel extends RelationModel{
    protected $_link = array(
        'User'=>array(
            'mapping_type'  => self::BELONGS_TO,
            'class_name'    => 'Users',
            'foreign_key'   => 'uid',
            'mapping_name'  => 'user',
        ),
        'Shop' => array(
            'mapping_type'  => self::BELONGS_TO,
            'class_name'    => 'Shop',
            'foreign_key'   => 'gid',
            'mapping_name'  => 'Shop',
        ),
    );
}