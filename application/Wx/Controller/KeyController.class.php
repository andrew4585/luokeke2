<?php namespace Wx\Controller;

use Wx\Model\WxSourceCatModel;

class KeyController extends IndexController
{
    public $model_cat;
    public $modelSourceCat;
    public $modelkeyword;
    public function __construct()
    {
        parent::__construct();
        $this->model_cat        = D("WxKeywordCat");
        $this->modelSourceCat   = D("WxSourceCat");
        $this->modelkeyword     = D("WxKeyword");
    }

    //公共参数
    function commonParam()
    {
        //分类列表
        $list = $this->model_cat->getCategories();
        $keyCat = array();
        foreach ($list as $key=>$value){
            $keyCat[$value['id']] = $value['cat_name'];
        }
        $this->assign('keycat',$keyCat);
        $this->assign("category",$list);
    }


    function index()
    {
        $this->_lists();
        $type = array(0=>'文字',3=>'多图文');
        $this->assign('type',$type);
        $this->commonParam();
        $this->display();
    }

    private  function _lists(){
        //status=1,表示未删除，0表示已删除
        $where_ands =array("status=1");
        //listorder：排序，post_date:发布时间
        $order		="listorder ASC,post_date DESC";
        $fields=array(
            'cid'		=> array("field"=>'cid','operator'=>'=','type'=>'int'),
            'start_time'=> array("field"=>"post_date","operator"=>">=",'type'=>'time'),
            'end_time'  => array("field"=>"post_date","operator"=>"<=",'type'=>'time'),
            'keyword'   => array("field"=>"post_title","operator"=>"like",'type'=>'string'),
        );
        foreach ($fields as $param =>$val){
            if (!empty($_REQUEST[$param])) {

                $operator=$val['operator'];		//操作
                $type	 =$val['type'];			//参数类型
                $field   =$val['field'];		//字段名
                $get	 =$_REQUEST[$param];	//数据
                if($operator=="like"){
                    $get="'%$get%'";
                }elseif ($type=='time'){
                    $get=strtotime($get);
                }elseif ($type=='string'){
                    $get="'$get'";
                }
                array_push($where_ands, "$field $operator $get");
            }
        }
        $where= join(" and ", $where_ands);

        $count=$this->modelkeyword->where($where)->count();

        $page = $this->page($count, 20);

        $list =$this->modelkeyword ->where($where)
            ->limit($page->firstRow, $page->listRows)
            ->order($order)->select();
        $this->assign("Page", $page->show('Admin'));
        $this->assign("formget",$_REQUEST);
        $this->assign("list",$list);
    }

    public function add()
    {
        if(IS_POST){
            $data = array();
            $data['post_title']     = $_POST['post_title'];
            $data['cid']            = $_POST['cid'];
            $data['type']           = $_POST['type'];
            $data['sourceid']       = $_POST['sourcetitle'];
            $data['post_date']      = time();
            $result = $this->modelkeyword->addKeyword($data);
            return ($result!=0 && is_numeric($result)) ? $this->success('添加成功!', U("Key/index")) : $this->error($result);
        }else{
            $this->commonParam();
            $sourceCat = $this->modelSourceCat->getCategories();
            $this->assign('sourcecat',$sourceCat);
            $this->display();
        }
    }

    public function edit($id)
    {
        if(IS_POST){
            $data = array();
            $data['post_title']     = $_POST['post_title'];
            $data['cid']            = $_POST['cid'];
            $data['type']           = $_POST['type'];
            $data['sourceid']       = $_POST['sourcetitle'];
            $data['post_date']      = time();
            $result = $this->modelkeyword->updateKeyword($id,$data);
            return $result ? $this->success('更新成功!', U("Key/index")) : $this->error('更新失败!');
        }else{
            $info = $this->modelkeyword->getKeyword($id);
            $source = D("WxSource");
            $sourcetitle = $source->where("id = $info[sourceid]")->relation(TRUE)->find();
            $sourcelist = $this->modelSourceCat->relation(TRUE)->where("id = $sourcetitle[cid]")->find();
            $this->assign('sourcelist',$sourcelist);
            $this->assign('sourcetitle',$sourcetitle);
            $this->assign('info',$info);
            $this->commonParam();
            $sourceCat = $this->modelSourceCat->getCategories();
            $this->assign('sourcecat',$sourceCat);
            $this->display();
        }
    }

    public function getSource()
    {
        //ajax 获取 素材分类下的素材列表
        if(IS_POST) {
            $type           = $_POST['type'];
            $cid            = $_POST['cid'];
            $modelSource    = D("WxSource");
            $sourcelist     = $modelSource->where("cid = $cid and type = $type")->order('listorder')->select();
            echo  json_encode($sourcelist);
        }

    }

    function delete(){
        if(isset($_GET['id'])){
            $id = intval(I("get.id"));
            $data['status']=0;
            if ($this->modelkeyword->where("id=$id")->save($data)) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
        if(isset($_POST['ids'])){
            $ids=join(",",$_POST['ids']);
            $data['status']=0;
            if ($this->modelkeyword->where("id in ($ids)")->save($data)) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }


    public function listorders() {
        $status = parent::_listorders($this->modelkeyword);
        if ($status) {
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }
    //分类管理
    public function cindex()
    {
        $this->commonParam();
        $this->display();
    }

    public function cadd()
    {
        if(IS_POST){
            $result = $this->model_cat->addCat($_POST);
            return is_numeric($result) ? $this->success('添加成功!', U("Key/cindex")) : $this->error($result);
        }else{
            $this->display();
        }
    }

    public function cedit($id)
    {
        if(IS_POST){
            $result = $this->model_cat->updateCat($id,$_POST);
            return is_numeric($result) ? $this->success('更新成功!', U("Key/cindex")) : $this->error($result);
        }else{
            $cat = $this->model_cat->getcat($id);
            $this->assign($cat);
            $this->display();
        }
    }
    //分类排序
    function clistorders(){
        $status = parent::_listorders($this->model_cat);
        if ($status) {
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }
    //删除分类
    function cdelete($id){
        if ($this->model_cat->delCat($id)) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
}