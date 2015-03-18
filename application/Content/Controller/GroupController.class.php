<?php
namespace Content\Controller;
use Common\Controller\AdminbaseController;
class GroupController extends AdminbaseController{
	protected $model_cate;
	protected $model_obj;
	protected $imgFolder;
	function _initialize() {
		parent::_initialize();
		$this->imgFolder	= "Group";
		$this->model_cate	= D("GroupCat");
		$this->model_obj	= D("Group");
	}
	function index(){
		//列表数据
		$this->_lists();
		$this->display();
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
	function add(){
		if(IS_POST){
			$_POST['post_date']= strtotime($_POST['post_date']);
			$_POST['post_content']=htmlspecialchars($_POST['post_content']);
			$_POST['post_pic'] = $this->removeUploadImage($this->imgFolder, $_POST['post_pic']);
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
			$_POST['post_content']=htmlspecialchars($_POST['post_content']);
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
// 			$this->commonParam();
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
	//--------------------------------------------category--------------------------------
	
	//分类列表
	function cindex(){
		$cats=$this->model_cate->order("listorder")->select();
		$this->assign("category",$cats);
		$this->display();
	}
	
	//添加分类
	public function cadd() {
		if(IS_POST){
			if ($this->model_cate->create()) {
				if ($this->model_cate->add($_POST)!==false) {
					$this->success("添加成功！", U("Group/cindex"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->model_cate->getError());
			}
		}else{
			$this->display();
		}
	}
	
	//编辑分类
	function cedit(){
		if(IS_POST){
			if ($this->model_cate->create()) {
				if ($this->model_cate->save($_POST)!==false) {
					$this->success("保存成功！", U("Group/cindex"));
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->model_cate->getError());
			}
		}else{
			$id= intval(I("get.id"));
			$ad1cat=$this->model_cate->where("id=$id")->find();
			$this->assign($ad1cat);
			$this->display();
		}
	
	}
	
	//删除分类
	function cdelete(){
		$id = intval(I("get.id"));
		if ($this->model_cate->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
	//分类排序
	function clistorders(){
		$status = parent::_listorders($this->model_cate);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
}
?>