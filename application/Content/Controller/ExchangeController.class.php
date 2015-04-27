<?php
namespace Content\Controller;

use Common\Controller\AdminbaseController;

class ExchangeController extends AdminbaseController{

    protected $model_obj;

    function _initialize() {
        parent::_initialize();
        $this->model_obj	= D("Exchange");
    }

    function index() {
        //列表数据
        $this->_lists();
        $this->display("Exchange/index");
    }

    private  function _lists(){
        //post_date:发布时间
        $where_ands =array();
        $order		="post_date DESC";
        $fields=array(
            'start_time'=> array("field"=>"post_date","operator"=>">=",'type'=>'time'),
            'end_time'  => array("field"=>"post_date","operator"=>"<=",'type'=>'time'),
            'keyword'   => array("field"=>"name","operator"=>"like",'type'=>'string'),
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


    //显示操作
    function recommend(){

        //批量显示
        if(isset($_POST['ids']) && $_GET["recommend"]){
            $data["status"]=1;
            $ids=join(",", $_POST['ids']);

            if ( $this->model_obj->where("id in ($ids)")->save($data)) {
                $this->success("成功！");
            } else {
                $this->error("失败！");
            }

        }
        //批量取消显示
        if(isset($_POST['ids']) && $_GET["unrecommend"]){
            $data["status"]=0;
            $ids=join(",", $_POST['ids']);
            if ( $this->model_obj->where("id in ($ids)")->save($data)) {
                $this->success("成功！");
            } else {
                $this->error("失败！");
            }
        }
        if(isset($_GET['id'])){
            $data['status'] = $_GET['recommended'];
            $result=$this->model_obj->where(array("id" => $_GET['id']))->save($data);
            if ($result) {
                $this->success("success");
            } else {
                $this->error("fail");
            }
        }
    }
}