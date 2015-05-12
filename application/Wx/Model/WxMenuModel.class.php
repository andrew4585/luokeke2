<?php
namespace Wx\Model;
use Common\Model\CommonModel;

use Think\Log;

class WxMenuModel extends CommonModel{

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
