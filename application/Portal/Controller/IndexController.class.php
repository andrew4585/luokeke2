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
// 			dump($this->_getAd("home_head"));exit;
			//首页头部轮播
			$this->assign("home_head",$this->_getAd("home_head"));
			//首页中部轮播
			$this->assign("home_middle",$this->_getAd("home_middle"));
			//最新活动
			$active = $this->getHomeContent("Active",false,array(),5);
			$this->assign("active",$active);
			//主题作品
			$ptheme = $this->getHomeContent("Ptheme",true);
			$this->assign("ptheme",$ptheme);
			//团购套系
			$group = $this->getHomeContent("Group",false,array("post_price","post_num"));
 			$this->assign("group",$group);
			//客照
			$this->assign("pcustom",$this->getHomeContent("Pcustom",true));
			//婚纱礼服
			$this->assign("dress",$this->getHomeContent("Dress",false,array("rent","sale_price")));
			//好评
			$this->assign("good",$this->getHomeContent("Good",false,array("head_img","post_excerpt")));
			//婚嫁常识
			$article_cat = D("Article_cat");
			$articleC = $article_cat->limit(0,5)->select();
			$list = $this->getHomeContent("Article",false,array("post_excerpt","post_date","cid"),6);
			$articles = array();
			for ($i=0;$i<count($articleC);$i++){
				for($j=0;$j<count($list);$j++){
					if($list[$j]['cid']==$articleC[$i]['id']){
						$articles[$i]['article'][] 	= $list[$j];					
					}
				}
				$articles[$i][cat] 	= $articleC[$i]['cat_name'];
			}
			
			//dump($articles);
			$this->assign("articles",$articles);
		}catch (\Exception $e){
			$this->error($e->getMessage());
		}
    	$this->display("index");
    }   
	
    /**
     * 获得首页推荐内容
     * @param string  $model_class 模型名（对应数据库表名）
     * @param bool 	  $siteCharge  是否根据站点判断,默认为false
     * @param array	  $extra	       额外字段,自带字段：id,post_pic,post_title,post_url
     * @param int	  $limitNumber 数据条数，默认：20条
     */
    public function getHomeContent($model_class,$siteCharge=false,$extra=array(),$limitNumber=20){
    	$model		= D($model_class);
    	$fieldArr	= array("id","post_pic","post_title","post_url");
    	//istop:置顶，status：是否显示
    	$whereArr	= array("istop=1","status=1",);
    	if($siteCharge){
    		array_push($whereArr, "site_id=$this->siteId");
    	}
    	if(!empty($extra)){
    		$fieldArr = array_merge($fieldArr,$extra);
    	}
    	$where		= join(" and ",$whereArr);
    	$field		= join(",",$fieldArr);
    	$data		= $model->field($field)->where($where)->order("listorder")->limit(0,$limitNumber)->select();
    	return $data;
    }
    
    /**
     * 二级首页推荐信息
     * @param string  $model_class 模型名（对应数据库表名）
     * @param bool 	  $siteCharge  是否根据站点判断,默认为false
     * @param array	  $extra	       额外字段,自带字段：id,post_pic,post_title,post_url
     * @param int	  $limitNumber 数据条数，默认：20条
     * <br/>@适用范围：含有recommended字段
     */
    public function getRecommended($model_class,$siteCharge=false,$extra=array(),$limitNumber=20){
    	$model		= D($model_class);
    	$fieldArr	= array("id","post_pic","post_title","post_url");
    	//istop:置顶，status：是否显示
    	$whereArr	= array("status=1");
    	if($siteCharge){
    		array_push($whereArr, "site_id=$this->siteId");
    	}
    	if(!empty($extra)){
    		$fieldArr = array_merge($fieldArr,$extra);
    	}
    	$where		= join(" and ",$whereArr);
    	$field		= join(",",$fieldArr);
    	$data		= $model->field($field)->where($where)->order("recommended desc,listorder")->limit(0,$limitNumber)->select();
    	return $data;
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
    							->order("listorder desc")->select();
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
