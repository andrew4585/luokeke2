<?php namespace Common\Model;

use Think\Model\RelationModel;

class WxSourceModel extends RelationModel
{
    protected $_link = array(
        'WxArticle' => array(
            'mapping_type'  => self::HAS_MANY,
            'class_name'    =>'WxArticle',
            'foreign_key'   =>'cid',
            'mapping_name'  =>'article',
        ),
        'WxSourceCat' => array(
            'mapping_type'  => self::BELONGS_TO,
            'class_name'    => 'WxSourceCat',
            'foreign_key'   => 'cid',
            'mapping_name'  => 'cat',
        )
    );
}