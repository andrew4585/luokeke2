<?php
/**
 * 后台微信公共类
 */
namespace Wx\Controller;
use Common\Controller\AdminbaseController;
class IndexController extends AdminbaseController {
	//微信js类
	protected $jssdk;
	//微信帮助类
	protected $thinkWechat;
	
	protected $appid;
	
	protected $appsecret;
	
	protected $model_config;
	
	function _initialize() {
		parent::_initialize();
		$this->initMenu();
	}
	
	/**
	 * 获取微信js类
	 */
	function getJssdk(){
		if(!$this->jssdk){
			import("Think.WX.jssdk");
			$this->jssdk= new \JSSDK($this->getAppid(), $this->getAppsecret());
		}
	}
    
	/**
	 * 获取微信帮助类
	 */
	function getThinkWechat(){
		if(!$this->thinkWechat){
			import("Think.WX.ThinkWechat");
			$this->thinkWechat = new \ThinkWechat();
		}
	}
	
	function getAppid(){
		if(!$this->appid)
			$this->appid = $this->getConfigValue("appid");
		return $this->appid;
	}
	
	function getAppsecret(){
		if(!$this->appsecret)
			$this->appsecret = $this->getConfigValue("appsecret");
		return $this->appsecret;
	}

	/**
	 * 获取配置信息
	 * @param string $key 键
	 */
	function getConfigValue($key){
		if(!$this->model_config) 
			$this->model_config=D("WxConfig");
		
		return $this->model_config->val($key);
	}
}

