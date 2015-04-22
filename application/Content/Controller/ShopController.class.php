<?php
namespace Content\Controller;

use Common\Controller\AdminbaseController;

/**
 * 商品管理
 * @author duostec
 *
**/
class ShopController extends  AdminbaseController{

    protected $model_cate;
    protected $model_obj;
    protected $imgFolder;

    function _initialize() {
        parent::_initialize();
        $this->imgFolder	= "Shop";
        $this->model_cate	= D("ShopCat");
        $this->model_obj	= D("Shop");
    }

    function index() {
        //列表数据
        $this->_lists();
        $this->display("Shop/index");
    }

    private  function _lists(){
        //status=1,表示文章未删除，0表示文章已删除
        $where_ands =array("status=1");
        //istop:首页置顶，recommended：推荐，listorder：排序，post_date:发布时间
        $order		="recommended desc,listorder ASC,post_date DESC";
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
}