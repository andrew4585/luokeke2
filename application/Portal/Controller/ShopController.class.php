<?php
namespace Portal\Controller;

class ShopController extends IndexController
{
    private $model_shop;
    protected $model_cat;

    public function __construct()
    {
        parent::__construct();
        $this->model_shop	= D("Shop");
        $this->model_cat 	 = D('ShopCat');
        $this->assign('bannerpic',1);
    }

    private function common()
    {
        //广告
        $this->assign("servePromise",$this->_getAd("servePromise"));
        //分类显示
        $category = $this->model_cat->order('listorder')->select();
        $this->assign('category',$category);
        $exchange = D('Exchange');
        $new_exchange = $exchange->relation(true)->where("status=1")->order("post_date DESC")->limit(0,5)->select();
        $this->assign('new_exchange',$new_exchange);
        $user       = D('Users');
        $field      = 'avatar,user_login,score';
        $score      = $user->field($field)->order("score DESC")->limit(0,10)->select();
        $this->assign('score',$score);
        $changeTypes = array(0=>'已订单用户',1=>'全部用户');
        $this->assign('changeTypes',$changeTypes);


    }

    public function index()
    {

        $this->common();
        $cid = I("get.cid",'0','intval');
        $whereArr = empty($cid)?array("status=1"):array("status=1","cid=$cid");
        $sort = I('get.sort','','intval');
        if(!empty($sort)){
            if($sort == 1){
                $order = "post_score ASC";
            }else{
                $order = "post_like DESC,remain ASC";
            }
        }else{
            $order  = "listorder ASC,post_date DESC";
        }
        $where		= join(" and ",$whereArr);

        import('Page');
        //总数
        $total		= $this->model_shop->where($where)->count();
        $Page       = new \Page($total,12);
        $Page->SetPager('Home', '{first}{prev}&nbsp;{liststart}{list}{listend}&nbsp;{next}{last}', array("listlong" => "6", "first" => "首页", "last" => "尾页", "prev" => " < ", "next" => " > ", "list" => "*", "disabledclass" => ""));
        $list 		= $this->model_shop->where($where)
            ->order($order)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $this->assign('list',$list);
        $this->assign('page',$Page->show("Home"));
        $this->assign("cid",$cid);
        $this->display();
    }

    public function info()
    {
        $id         = I('get.id',0,'intval');
        if(empty($id)) $this->error('参数丢失');
        $where      = "id=$id and status=1";
        $info = $this->model_shop->relation(true)->where($where)->find();
        if(!$info) $this->error('该页面不存在 ！',U('Portal/shop/index'));
        $this->assign('info',$info);
        //兑换流程图片
		$this->assign("exchangeProcess",$this->_getSingle(31));
        //图片信息
		$photo	= json_decode($info['smeta'],true);
		$this->assign("photo",$photo['photo']);
        $this->common();
        $this->assign("model_table","Shop");
        $this->display();
    }

    public function ajax_like(){
        $this->_like($this->model_shop);
    }
}