<?php
namespace Portal\Controller;
use Think\Log;

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
		$this->getSiteData();
		$this->getDate();
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
			
			//广告位
			$this->assign("promise",$this->_getAd("promise"));
			$this->assign("servePromise",$this->_getAd("servePromise"));
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
     * @param string  $model 	        模型
     * @param bool 	  $siteCharge  是否根据站点判断,默认为false
     * @param array	  $extra	       额外字段,自带字段：id,post_pic,post_title,post_url
     * @param array	  $extraWhere  查询条件
     * @param int	  $limitNumber 数据条数，默认：20条
     * <br/>@适用范围：含有recommended字段
     */
    public function getRecommended($model,$siteCharge=false,$extra=array(),$extraWhere=array(),$limitNumber=16){
    	$fieldArr	= array("id","post_pic","post_title","post_url");
    	//istop:置顶，status：是否显示
    	$whereArr	= array_merge($extraWhere,array("status=1"));
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
     * 列表数据
     * @param string  $model 	        模型
     * @param bool 	  $siteCharge  是否根据站点判断,默认为false
     * @param array	  $extra	       额外字段,自带字段：id,post_pic,post_title,post_url
     * @param array	  $extraWhere  查询条件
     * @param int	  $count	        分页条数，默认：16条
     */
    public function _list($model,$siteCharge=false,$extra=array(),$extraWhere=array(),$count=16,$order=''){
    	$fieldArr	= array("id","post_pic","post_title","post_url");
    	$whereArr	= array_merge($extraWhere,array("status=1"));
    	if($siteCharge){
    		array_push($whereArr, "site_id=$this->siteId");
    	}
    	if(!empty($extra)){
    		$fieldArr = array_merge($fieldArr,$extra);
    	}
    	$where		= join(" and ",$whereArr);
    	$field		= join(",",$fieldArr);
    	$order		= empty($order)?"listorder ASC,post_date DESC":$order;
    	// 导入分页类
    	import('Page');
    	//总数
    	$total		= $model->where($where)->count();
    	$Page       = new \Page($total,$count);
    	$Page->SetPager('Home', '{first}{prev}&nbsp;{liststart}{list}{listend}&nbsp;{next}{last}', array("listlong" => "6", "first" => "首页", "last" => "尾页", "prev" => " < ", "next" => " > ", "list" => "*", "disabledclass" => ""));
    	$list 		= $model->field($field)->where($where)
    						->order($order)
    						->limit($Page->firstRow.','.$Page->listRows)
    						->select();
    	$this->assign("list",$list);
    	$this->assign('page',$Page->show("Home"));
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
     *获取站点导航数据 
     */
	protected function getSiteData(){
		$model_site = D("site");
		$data 		= $model_site->select();
		$this->assign("siteData",$data);
		//dump($a)
	}
    
    /**
     * 设置当前站点编号
     */
    protected function setSiteId(){
    	$site = &$this->siteId;
    	$site = I("siteid",0,"intval");
    	$model_site	= D("Site");		//站点模型
    	//判断用户是否选择站点
    	if(empty($site)){
    		$site = cookie("siteid");
    		//判断cookie中是否保存siteid
    		if(empty($site)){
				$site		= $model_site->order("listorder,id")->getField("id");
    		}
    	}
    	//判断是否取得站点编号
    	if(!$site){
    		$this->error("站点获取失败，请检查后台是否填写站点信息");
    	}else{
    		cookie("siteid",$site);
    		$site_name	= $model_site->where("id=$site")->getField("site_name");
    		$this->assign("site_name",$site_name);
    		$this->assign("siteid",$site);
    	}
    }
    /**
     * 右侧导航作品
     */
    public function info_right(){
    	$model_photo	= D("Photo");
    	$model_pCate	= D("PhotoCat");
    	$cateList		= $model_pCate->field("id,cat_name")->order("listorder")->select();
    	$right			= array();
    	$where			= "recommended=1 and site_id={$this->siteId} and status=1";
    	foreach($cateList as $item){
    		$whereAlias	    = $where." and cid=".$item['id'];
    		$item['list']	= $model_photo	->field("id,post_title,post_excerpt")
    		->where($whereAlias)->order("listorder,post_date desc")
    		->select();
    		array_push($right, $item);
    	}
    	//右侧导航---广告
    	$this->assign("info_right",$this->_getAd("info_right"));
    	//右侧导航---作品列表
    	$this->assign("rightPhoto",$right);
    }
    
    /**
     * 获取首页图片日历
     */
    private function getDate(){
		  $date = date("md");
		  $num = str_split($date,1);
		  $this->assign("num",$num);
    }
    
    /**
     * 天气获取
     */
    public function getWeather(){
    	header("Content-type:text/html;charset=utf-8");
    	$weather = file_get_contents("http://www.weather.com.cn/adat/cityinfo/101070201.html");
    	echo $weather;
    }
    
    /**
     * 用户点击“喜欢”按钮
     * @param object model  数据模型
     * @return boolean
     */
    public function _like($model){
    	if(IS_AJAX){
	    	try{
	    		//喜欢的文章编号
	    		$id	= I("post.id",0,'intval');
	    		if(empty($id)) throw new Exception("文章编号丢失");
	    		$result	= $model->where("id=$id")->setInc("post_like",1);
	    		if($result){
	    			$this->success('操作成功');
	    		}else{
	    			$this->error('操作失败');
	    		}
	    	}catch (\Exception $e){
	    		$this->error($e->getMessage());
	    	}
			
		}else{
			alert("非法操作");
		}
    }
    
    //-----------------------------评论部分  start--------------------
    /**
     * 获取评论列表
     * @param string postTable   评论内容所在表，不带表前缀
     * @param int	 postId		   内容编号
     * @param int    lastid		   上一页，最后评论编号
     */
    public function getCommentList($postTable,$postId){
    	
    	$model_comments = D("Comments");
    	$postTable		= empty($postTable)?I("post.postTable"):$postTable;
    	$postId			= empty($postId)?I("post.postId"):$postId;
    	$lastid			= I("post.lastid",0,'intval');
    	//参数非空验证
    	if(empty($postTable)||empty($postId)){
    		$this->error("参数缺失");
    	}
    	//sql where条件
    	$where	= "post_table = '$postTable' and post_id = $postId and status=1";
    	if(!empty($lastid)){
    		$where	.= " and id<$lastid"; 
    	}
    	
    	$list	= $model_comments->relation(true)->where($where)->order("id desc")->limit(0,10)->select();
    	//判断是否是ajax调用
    	if(IS_AJAX){
    		$tmpl	= $this->createCommentTmpl($list);
    		$this->success($tmpl);
    	}else{
    		$this->assign("comments",$list);
    	}
    }
    
    /**
     * 添加评论
     */
    public function commentAdd(){
    	if(IS_POST){
    		$model_comment = D("Comments");
    		$_POST['uid']  = $_SESSION['user']['id'];
    		$_POST['createtime'] = time();
    		if(empty($_POST['uid'])) $this->error("请登录后评论");
    		if($model_comment->create()){
    			$result = $model_comment->add($_POST);
    			if($result){
    				$table = $_POST['post_table'];
    				try{
    					$model = D($table);
    					$Cnum = $model->where("id={$_POST['post_id']}")->setInc('comment_count',1);
    					$this->success($Cnum);
    				}catch(\Exception $e) {
    					$this->error($e->getMessage());
    				}
    				$this->success("评论成功");
    			}else{
    				$this->error("评论失败");
    			}
    		}
    	}else{
    		$this->error("非法操作");
    	}
    }
    
    /**
     * 生成评论的静态页面（字符串）
     */
    public function createCommentTmpl($list){
    	$str = '';
    	foreach ($list as $item){
    		$str 	= "<div class='work_list' data-id='{$item['uid']}'>
					       <div class='work_l'>
	    						<div class='work_l_ico_x'>";
    		if(empty($item['user']['avatar'])){
	    		$str	.="<img src='".C("TMPL_PARSE_STRING.__STATICS__")."images/tpl/comment/user_default.png' alt=''/>";
    		}else{
    			$str	.="<img src='".sp_get_user_avatar_url($item['user']['avatar'])."' alt=''/>";
    		}
    		$str	.="</div></div>
					       <div class='work_r'>
					           <div class='work_r_up_x'>
					               <ul>
					                   <li style='margin:0'>".$item['user']['user_nicename']."</li>
					                   <li>".date('Y-m-d',$item['createtime'])."</li>
					               </ul>
					           </div>
					           <div class='work_r_down_x'>
					              <p>{$item['content']}</p>
					           </div>
					          <div class='clear'></div>
					       </div>
					   </div>
	    				";
    	}
    	return $str;
    }
    
    //-----------------------------评论部分  end--------------------
    
    /**
     * 生成二维码
     */
    public function qrcode(){
    	$url	= $this->_getUri();
    	$name	= encrypt($url);
    	$path	= "./data/upload/qrcode/$name.png";
    	if(!file_exists($path)){
    		Vendor("phpqrcode.phpqrcode");
    		\QRcode::png($url,$path,'L',1000,2);
    	}
    	$this->assign("qrcode",$path);
    }
    
    /**
     * 获取当前页面网址
     */
    public function _getUri(){
    	$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    	return $url;
    }
}
