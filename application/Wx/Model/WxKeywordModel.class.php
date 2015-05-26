<?php namespace Wx\Model;

use Common\Model\CommonModel;
use Think\Model\RelationModel;
use Think\Log;

class WxKeywordModel extends CommonModel
{
    //自动验证
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('post_title', 'require', '关键字名称不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH ),
        array('sourceid', 'require', '素材ID不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH ),
        array('post_title','','该关键字已经存在！',0,'unique',1),
    );

    public function getList()
    {
        return $this->select();
    }
    //获取单个分类
    public function getKeyword($id)
    {
        return $this->where("id='".intval($id)."'")->find();
    }

    public function addKeyword($data)
    {
        if($this->create($data)) {
            return $this->add();
        }else{
            return $this->getError();
        }
    }

    public function updateKeyword($id,$data)
    {
        $result = $this->where("id='".intval($id)."'")->save($data);
        return $result;
    }
}