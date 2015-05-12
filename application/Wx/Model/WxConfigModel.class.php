<?php
namespace Wx\Model;
use Think\Log;
use Common\Model\CommonModel;

class WxConfigModel extends CommonModel{

	
	/**
	 * 获取公众账号全部基本信息
	 * @return multitype:unknown
	 */
	public function getBaseInfo(){
		$data=array();
		$infos=$this->where("`key`<>'navigation'")->select();
		foreach($infos as $item){
			$data[$item['key']]=$item['value'];
		}
		return $data;
	}
	
	/**
	 * 获取公众账号基本信息
	 * @param unknown_type $key
	 * @param unknown_type $val
	 * @return boolean|Ambigous 
	 */
	public function val($key,$val=""){
		try{
			$where['key'] = $key;
			if($val){			//更新value值
				$result = $this->where($where)->setField("value",$val);
				return $result;
			}else{				//获取value值
				$val = $this->where($where)->getField("value");
				return $val;
			}
		}catch (\Exception $e){
			Log::record($e->getMessage());
			return false;
		}
	}
	
	/**
	 * 修改信息
	 * @param unknown_type $data
	 */
	public function modify($data){
		foreach ($data as $k=>$v){
			$data['key']   = $k;
			$data['value'] = $v;
			$this->save($data);
		}
		return true;
	}
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
	
	
}
