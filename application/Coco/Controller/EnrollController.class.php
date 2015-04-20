<?php namespace Coco\Controller;

use Common\Controller\AdminbaseController;

class EnrollController extends AdminbaseController{

    protected $Enroll_obj;
    protected $cococat_obj;
    protected $imgFolder;

    function _initialize() {
        parent::_initialize();
        $this->imgFolder	= "Enroll";
        $this->cococat_obj	= D("CocoCat");
        $this->model_obj	= D("Enroll");
    }

    //公共参数
    function commonParam(){
        //分类列表
        $this->assign("categorys",$this->getCategory($this->cococat_obj));
    }

    function index(){
        //列表数据
        $this->_lists();
        $this->commonParam();
        $this->display();
    }

    private  function _lists(){
        //status=1,表示文章未删除，0表示文章已删除
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

        $count=$this->model_obj->where($where)->count();

        $page = $this->page($count, 20);

        $list =$this->model_obj ->relation(true)->where($where)
            ->limit($page->firstRow, $page->listRows)
            ->order($order)->select();
        $this->assign("Page", $page->show('Admin'));
        $this->assign("formget",$_REQUEST);
        $this->assign("list",$list);
    }

    function add(){
        if(IS_POST){
            $_POST['post_date']= strtotime($_POST['post_date']);
            $_POST['post_pic'] = $this->removeUploadImage($this->imgFolder, $_POST['post_pic']);
            $result=$this->model_obj->add($_POST);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
        }else{
            $this->commonParam();
            $this->display();
        }
    }

    public function edit(){
        if(IS_POST){
            $_POST['post_date']= strtotime($_POST['post_date']);
            $_POST['post_pic'] = $this->removeUploadImage($this->imgFolder, $_POST['post_pic']);
            $result=$this->model_obj->save($_POST);
            if ($result) {
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
        }else{
            $id=  $_REQUEST['id'];
            $info = $this->model_obj->where("id=$id")->find();
            $this->assign($info);
            $this->commonParam();
            $this->display();
        }
    }
    function delete(){
        if(isset($_GET['id'])){
            $id = intval(I("get.id"));
            $data['status']=0;
            if ($this->model_obj->where("id=$id")->save($data)) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
        if(isset($_POST['ids'])){
            $ids=join(",",$_POST['ids']);
            $data['status']=0;
            if ($this->model_obj->where("id in ($ids)")->save($data)) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }



//推荐操作
    function recommend(){
        //批量推荐
        if(isset($_POST['ids']) && $_GET["display"]){
            $data["recommended"]=1;
            $ids=join(",", $_POST['ids']);
            if ( $this->model_obj->where("id in ($ids)")->save($data)) {
                $this->success("显示成功！");
            } else {
                $this->error("显示失败！");
            }
        }
        //批量取消推荐
        if(isset($_POST['ids']) && $_GET["hide"]){
            $data["recommended"]=0;
            $ids=join(",", $_POST['ids']);
            if ( $this->model_obj->where("id in ($ids)")->save($data)) {
                $this->success("隐藏成功！");
            } else {
                $this->error("隐藏失败！");
            }
        }
        //单个推荐/取消推荐
        if(isset($_GET['id'])){
            $data['recommended'] = $_GET['recommended'];
            $result=$this->model_obj->where(array("id" => $_GET['id']))->save($data);
            if ($result) {
                $this->success("success");
            } else {
                $this->error("fail");
            }
        }
    }
    //投票情况显示

    public function vote(){
        $vote_model = D("Common/Vote");
        $where = array();
        $enroll_id=I("get.enroll_id");
        if(!empty($enroll_id)){
            $where['enroll_id']=$enroll_id;
        }
        $count=$vote_model->where($where)->count();
        $page = $this->page($count, 20);
        $vote=$vote_model
            ->relation(true)
            ->where($where)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order("time DESC")
            ->select();
        $this->assign("vote",$vote);
        $this->assign("Page", $page->show('Admin'));
        $this->display();
    }
    function vote_delete(){
        $vote_model = D("Common/Vote");
        if(isset($_GET['id'])){
            $id = intval(I("get.id"));
            if ($vote_model->where("id=$id")->delete()!==false) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
        if(isset($_POST['ids'])){
            $ids=join(",",$_POST['ids']);
            if ($vote_model->where("id in ($ids)")->delete()!==false) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

}
