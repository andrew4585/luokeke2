<?php namespace Common\Model;

use Think\Model\RelationModel;

class SingleModel extends RelationModel
{
    public $category = array(1=>"普通",4=>"普通（无轮播）",2=>"加载",3=>"图片");

    protected $_validate = array(
        array('post_title', 'require', '作品标题不能为空！', 1, 'regex', CommonModel::MODEL_BOTH  ),
    );

    protected function _before_write(&$data) {
        parent::_before_write($data);
    }

}
?>