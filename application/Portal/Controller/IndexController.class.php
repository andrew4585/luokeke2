<?php
namespace Portal\Controller;
use Common\Controller\HomeBaseController; 
/**
 * 首页/前台基类
 */
class IndexController extends HomeBaseController {
	
	protected $siteId ;			//站点编号

	
	public function __construct(){
		parent::__construct();
		$this->setSiteId();
		$this->getMenuData();
	}
	
    //首页
	public function index() {
		try{
			//首页头部轮播
			$this->assign("home_head",$this->_getAd("home_head"));
			//首页中部轮播
			$this->assign("home_middle",$this->_getAd("home_middle"));
			
		}catch (\Exception $e){
			$this->error($e->getMessage());
		}
    	$this->display("index");
    }   
	
    /**
     * 获得首页推荐内容
     * @param string $model_class 模型名（对应数据库表名）
     */
    public function getHomeContent($model_class){
    	$model	= D($model_class);
    	
    }
    
    /**
     * 获取广告位信息
     * @param string $status 广告位分类标识
     */
    public function _getAd($status){
    	$model_ad = D("Ad1");
    	$model_cat= D("Ad1Cat");
    	//获取广告分类编号
    	$cid	  = $model_cat	->where("cat_idname='$status'")->getField("cid");
    	//广告数据
    	$data	  = $model_ad	->where("site_cid=$this->siteId and ad_cid=$cid")
    							->order("listorder")->select();
    	return $data;
    }
    
    
    /**
     * 获取导航菜单数据
     */
    protected function getMenuData(){
    	$model_nav	= D("Nav");
    	$data		= $model_nav->relation(true)->where("cid=$this->siteId and parentid=0")->select();
		$this->assign("menuData",$data);    	
    }
    
    /**
     * 设置当前站点编号
     */
    protected function setSiteId(){
    	$site = &$this->siteId;
    	$site = I("siteid",0,"intval");
    	//判断用户是否选择站点
    	if(empty($site)){
    		$site = cookie("siteid");
    		//判断cookie中是否保存siteid
    		if(empty($site)){
				$model_site	= D("Site");		//站点模型
				$site		= $model_site->order("listorder,id")->getField("id");
    		}
    	}
    	//判断是否取得站点编号
    	if(!$site){
    		$this->error("站点获取失败，请检查后台是否填写站点信息");
    	}else{
    		cookie("siteid",$site);
    	}
    }
}


