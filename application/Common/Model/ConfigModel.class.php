<?php
namespace Common\Model;
use Think\Log;

use Common\Model\CommonModel;
class ConfigModel extends CommonModel{
	
	
	public function val($key,$val=''){
		try{
			$where	= "key='$key'";
			if($val){			//获取value值
				$val = $this->where($where)->getField("value");
				return $val;
			}else{				//更新value值
				$result = $this->where($where)->setField("value",$val);
				return $result;
			}
		}catch (\Exception $e){
			Log::record($e->getMessage());
			return false;
		}
	}
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
	
	
}
