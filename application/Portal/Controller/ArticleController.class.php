<?php namespace Portal\Controller;

class ArticleController extends IndexController {

	protected $mode_cat;
	protected $model_article ;

	public function __construct(){
		parent::__construct();
		$this->model_article = D('Article');
		$this->model_cat 	 = D('article_cat');
		//广告
		//美态
		$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
		//3个摆放在一起的二级页面广告位
		$this->assign("second_page_3",$this->_getAd("second_page_3"));
		$this->assign("servePromise",$this->_getAd("servePromise"));
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

	private function Article_common(){
		//获取文章分类
		$category = $this->model_cat->order('listorder')->select();
		//获取热门资讯
		$hot_article = $this->getRecommended('article',false,array(),array(),10);
		$this->assign('category',$category);
		$this->assign('hot_article',$hot_article);
		//右侧广告
		$Ad = D('Ad1');
		$article_ad = $Ad->where('ad_cid=19')->order('listorder')->select();
		$this->assign('article_ad',$article_ad);
		//列表自增
		$this->assign('listnum',1);
	}

	public function lists() {
		$this->Article_common();
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
		if(!empty($info['post_url'])){
			header("location:".$info['post_url']);
		}
		$this->Article_common();
		//获取本页面的url
		$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$this->assign("url",$url);
		//二维码
		$this->qrcode();
		$this->assign("model_table","Article");
		$this->display('info');
	}

	public function ajax_like(){
		$this->_like($this->model_article);
	}
}