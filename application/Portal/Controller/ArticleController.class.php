<?php 
namespace Portal\Controller;

class ArticleController extends IndexController {

	protected $model_cat;
	protected $model_article ;

	public function __construct(){
		parent::__construct();
		$this->model_article = D('Article');
		$this->model_cat 	 = D('ArticleCat');
	}

	private function Article_common(){
		//广告
		//美态
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		$this->assign("servePromise",$this->_getAd("servePromise"));
		//获取文章分类
		$category = $this->model_cat->order('listorder')->select();
		//获取热门资讯
		$hot_article = $this->getRecommended('article',false,array(),array(),10);
		$this->assign('category',$category);
		$this->assign('hot_article',$hot_article);
		//右侧广告
		$this->assign('article_ad',$this->_getAd("article_right"));
	}

	public function lists() {
		$this->Article_common();
		$cid		= I("get.cid",0,'intval');
		if(empty($cid)){
			$cid	= $this->model_cat->getField("id");
		}
		$fieldArr	= array("id","post_pic","cid","post_author","post_title","post_like","post_share","post_excerpt","post_date");
		$whereArr	= array("cid=$cid","status=1");
		$where		= join(" and ",$whereArr);
		$field		= join(",",$fieldArr);
		$order		= "listorder ASC,post_date DESC";
		// 导入分页类
		import('Page');
		//总数
		$total		= $this->model_article->where($where)->count();
		$Page       = new \Page($total,5);
		$Page->SetPager('Home', '{first}{prev}&nbsp;{liststart}{list}{listend}&nbsp;{next}{last}', array("listlong" => "6", "first" => "首页", "last" => "尾页", "prev" => " < ", "next" => " > ", "list" => "*", "disabledclass" => ""));
		$list 		= $this->model_article->relation(true)->field($field)->where($where)
							->order($order)
							->limit($Page->firstRow.','.$Page->listRows)
							->select();
		//获取每篇文章的二维码
		for ($i=0;$i<5;$i++){
			if(empty($list[$i]))	break;
			$url = 'http://'.$_SERVER['HTTP_HOST'].U("Portal/Article/info")."/id/".$list[$i]['id'];
			$list[$i]['qrpath'] = $this->qrcode($url);
		}
		$this->assign("list",$list);
		$this->assign('page',$Page->show("Home"));
		$this->assign("cid",$cid);
		$this->assign("model_table","Article");
		$this->display('list');
	}

	public function info(){
		$id			= I("get.id",0,'intval');
		if(empty($id)){
			$this->error("参数丢失");
		}
		$where		= "id=$id and status=1";
		$info		= $this->model_article->relation(true)->where($where)->find();
		if(!$info){
			$this->error('该页面不存在 ！',U('Portal/article/lists'));
		}
		$this->assign('info',$info);
		$this->assign('cid',$info['cid']);
		if(!empty($info['post_url'])){
			header("location:".$info['post_url']);
		}
		$this->Article_common();
		//获取本页面的url
		$this->assign("url",$this->_getUri());
		//二维码
		$this->qrcode();
		$this->assign("model_table","Article");
		$this->display('info');
	}

	public function ajax_like(){
		$this->_like($this->model_article);
	}
	
	public function nav_index() {
		$m 			= M('article_cat');
		$msg 		= $m->where()->select();
		$item 		= array();
		foreach ($msg as $key=>$value){
			$item[] = array(
					"label" => "{$value['cat_name']}",
					"href" => U("Portal/Article/lists/cid/{$value['id']}")
			);
		}
		$nav_arr['name'] 	= "婚嫁分类";
		$nav_arr['items'] 	= $item;
		exit(json_encode($nav_arr));
	}
	
}