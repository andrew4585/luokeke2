<?php
namespace Wx\Model;
use Common\Model\CommonModel;

use Think\Log;

class WxMenuModel extends CommonModel{

    //自动验证
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('name', 'require', '菜单名称不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH ),
    );
	/**
	 * 获取微信导航的所有子导航
	 * @param unknown_type $fid
	 * @return NULL|multitype:
	 */
	public function getChildMenu($fid=0){
		$list = $this->where("fid=$fid")->order("listorder")->select();
		if(!$list) return null;
		$tempList = array();
		foreach($list as $item){
			$item['child'] = $this->getChildMenu($item['nav_id']);
			array_push($tempList, $item);
		}
		return $tempList;
	}
	
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
	
	
}
