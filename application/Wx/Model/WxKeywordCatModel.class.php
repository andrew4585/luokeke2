<?php namespace Wx\Model;

use Common\Model\CommonModel;
use Think\Log;

class WxKeywordCatModel extends CommonModel
{
    //自动验证
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('cat_name', 'require', '分类名称不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH ),
    );

    /**获取所有分类
     * @return mixed
     */
    public function getCategories()
    {
        return $this->order('listorder')->select();
    }
    //获取单个分类
    public function getcat($id)
    {
        return $this->where("id='".intval($id)."'")->find();
    }
    /**
     * @access public
     * @param $id 所更新的id
     * @param $data 所更新的数据
     * @return bool true false
     */
    public function updateCat($id,$data)
    {
        if($this->create($data)){
            return $this->where("id='".intval($id)."'")->save();
        }else{
            return $this->getError();
        }
        /*$result = $this->where("id='".intval($id)."'")->save($data);
        return $result;*/
    }

    /**增加一个分类
     * @param $data
     * @return bool
     */
    public function addCat($data)
    {
        if($this->create($data)){
            return $this->add();
        }else{
            return $this->getError();
        }
    }

    /**删除分类
     * @param $ids
     * @return mixed
     */
    public function delCat($ids)
    {
        return $result = $this->where("id in ($ids)")->delete();
    }

    protected function _before_write(&$data) {
        parent::_before_write($data);
    }

}