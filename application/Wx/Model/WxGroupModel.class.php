<?php namespace Wx\Model;

use Think\Log;
use Think\Model\RelationModel;

class WxGroupModel extends RelationModel
{
    protected $_link = array(
        'WxUser' => array(
            'mapping_type'  => self::HAS_MANY,
            'class_name'    =>'WxUser',
            'foreign_key'   =>'groupid',
            'mapping_name'  =>'users',
        )
    );
    public function getCats()
    {
        return $this->select();
    }
}