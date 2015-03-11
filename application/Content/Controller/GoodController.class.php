<?php
namespace Content\Controller;
use Common\Controller\AdminbaseController;
/**
 * 好评管理
 * @author duostec
 */
class GoodController extends AdminbaseController {
	
	protected $model_cate;
	protected $model_obj;
	protected $imgFolder;
	
	function _initialize() {
		parent::_initialize();
		$this->imgFolder	= "Good";
		$this->model_obj	= D("Good");
	}
	
	function index(){
		//列表数据
		$this->_lists();
		$this->display();
	}
	
	function add(){
		if(IS_POST){
			$_POST['post_date']= strtotime($_POST['post_date']);
			//好评图片
			$_POST['post_pic'] = $this->removeUploadImage($this->imgFolder, $_POST['post_pic']);
			//头像
			$_POST['head_img'] = $this->removeUploadImage($this->imgFolder."/head", $_POST['head_img']);
			$result=$this->model_obj->add($_POST);
			if ($result) {
				$this->success("添加成功！");
			} else {
				$this->error("添加失败！");
			}
		}else{
			$this->display();
		}
	}
	
	public function edit(){
		if(IS_POST){
			$_POST['post_date']= strtotime($_POST['post_date']);
			//好评图片
			$_POST['post_pic'] = $this->removeUploadImage($this->imgFolder, $_POST['post_pic']);
			//头像
			$_POST['head_img'] = $this->removeUploadImage($this->imgFolder."/head", $_POST['head_img']);
			$result=$this->model_obj->save($_POST);
			if ($result) {
				$this->success("编辑成功！");
			} else {
				$this->error("编辑失败！");
			}
		}else{
			$id=  $_REQUEST['id'];
			if(empty($id)){
				$this->redirect("Good/index");
			}
			$info = $this->model_obj->where("id=$id")->find();
			$this->assign($info);
			$this->display();
		}
	}
	
	//排序
	public function listorders() {
		$status = parent::_listorders($this->model_obj);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	
	private  function _lists(){
		//status=1,表示文章未删除，0表示文章已删除
		$where_ands =array("status=1");
		//listorder：排序，post_date:发布时间
		$order		="listorder ASC,post_date DESC";
		$fields=array(
				'start_time'=> array("field"=>"post_date","operator"=>">=",'type'=>'time'),
				'end_time'  => array("field"=>"post_date","operator"=>"<=",'type'=>'time'),
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
			
		$list =$this->model_obj ->where($where)
								->limit($page->firstRow, $page->listRows)
								->order($order)->select();
		$this->assign("Page", $page->show('Admin'));
		$this->assign("formget",$_REQUEST);
		$this->assign("list",$list);
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
}