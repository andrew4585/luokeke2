<?php namespace Wx\Model;

use Think\Log;
use Think\Model\RelationModel;

class WxGroupModel extends RelationModel
{
    protected $_link = array(
        'WxSource' => array(
            'mapping_type'  => self::HAS_MANY,
            'class_name'    =>'Wx',
            'foreign_key'   =>'cid',
            'mapping_name'  =>'Source',
        )
    );
    public function getCategories()
    {
        return $this->order('listorder')->select();
    }
}