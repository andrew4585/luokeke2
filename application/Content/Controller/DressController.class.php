<?php
namespace Content\Controller;
use Common\Controller\AdminbaseController;
/**
 * 婚纱礼服基类
 * @author duostec
 */
class DressController extends AdminbaseController {
	
	protected $model_cate;
	protected $model_obj;
	protected $imgFolder;
	protected $category = array(1=>"婚纱",2=>"礼服");
	protected $cate_id;		//分类编号
	
	function _initialize() {
		parent::_initialize();
		$this->imgFolder	= "Dress";
		$this->model_cate	= D("DressCat");
		$this->model_obj	= D("Dress");
	}
	
	//公共参数
	function commonParam(){
		//分类列表
		$categorys = $this->model_cate  ->field("id,cat_name")
										->where("category=$this->cate_id")
										->order("listorder")->select();
		$this->assign("categorys",$categorys);
		$this->assign("cname",$this->category[$this->cate_id]);
	}
	
	function _index(){
		
		//列表数据
		$this->_lists();
		$this->commonParam();
		$this->display("Dress/index");
	}
	
	function _add(){
		if(IS_POST){
			if(!empty($_POST['photos_alt']) && !empty($_POST['photos_url'])){
				foreach ($_POST['photos_url'] as $key=>$url){
					$photourl=$this->removeUploadImage($this->imgFolder,$url);
					$_POST['smeta']['photo'][]=array("url"=>$photourl,"alt"=>$_POST['photos_alt'][$key]);
				}
			}
			$_POST['smeta']['photo']=list_sort_by($_POST['smeta']['photo'],"alt");
			$_POST['smeta']=json_encode($_POST['smeta']);
			$_POST['category'] = $this->cate_id;
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
			$this->commonParam();
			$this->display("Dress/add");
		}
	}
	
	public function _edit(){
		$id=  $_REQUEST['id'];
		if(empty($id)) $this->error("The dress's id is lost!");
		if(IS_POST){
			if(!empty($_POST['photos_alt']) && !empty($_POST['photos_url'])){
				foreach ($_POST['photos_url'] as $key=>$url){
					$photourl=$this->removeUploadImage($this->imgFolder,$url);
					$_POST['smeta']['photo'][]=array("url"=>$photourl,"alt"=>$_POST['photos_alt'][$key]);
				}
			}
			$_POST['smeta']['photo']=list_sort_by($_POST['smeta']['photo'],"alt");
			$_POST['smeta']=json_encode($_POST['smeta']);
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
			
			$info = $this->model_obj->where("id=$id")->find();
			$this->assign($info);
			$this->assign("smeta",json_decode($info['smeta'],true));
			$this->commonParam();
			$this->display("Dress/edit");
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
		$where_ands =array("status=1","category=".$this->cate_id);
		//istop:首页置顶，recommended：推荐，listorder：排序，post_date:发布时间
		$order		="istop desc,recommended desc,listorder ASC,post_date DESC";
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
	
	//置顶操作
	function top(){
		//批量置顶
		if(isset($_POST['ids']) && $_GET["top"]){
			$data["istop"]=1;
			$ids=join(",", $_POST['ids']);
			if ( $this->model_obj->where("id in ($ids)")->save($data)) {
				$this->success("置顶成功！");
			} else {
				$this->error("置顶失败！");
			}
		}
		//批量取消置顶
		if(isset($_POST['ids']) && $_GET["untop"]){
			$data["istop"]=0;
			$ids=join(",", $_POST['ids']);
			if ( $this->model_obj->where("id in ($ids)")->save($data)) {
				$this->success("取消置顶成功！");
			} else {
				$this->error("取消置顶失败！");
			}
		}
		//单个置顶/取消置顶
		if(isset($_GET['id'])){
	    	$data['istop'] = $_GET['istop'];
	    	$result=$this->model_obj->where(array("id" => $_GET['id']))->save($data);
			if ($result) {
				$this->success("success");
			} else {
				$this->error("fail");
			}
		}
    	
	}
	//推荐操作
	function recommend(){
		//批量推荐
		if(isset($_POST['ids']) && $_GET["recommend"]){
			$data["recommended"]=1;
			$ids=join(",", $_POST['ids']);
			if ( $this->model_obj->where("id in ($ids)")->save($data)) {
				$this->success("置顶成功！");
			} else {
				$this->error("置顶失败！");
			}
		}
		//批量取消推荐
		if(isset($_POST['ids']) && $_GET["unrecommend"]){
			$data["recommended"]=0;
			$ids=join(",", $_POST['ids']);
			if ( $this->model_obj->where("id in ($ids)")->save($data)) {
				$this->success("取消置顶成功！");
			} else {
				$this->error("取消置顶失败！");
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
	
	
	//--------------------------------------------category--------------------------------
	
	//分类列表
	function cindex(){
		$cid = $_POST['cid'];
		if(!empty($cid)){
			$this->assign("cid",$cid);
			$cats=$this->model_cate->where("category=$cid")->order("listorder")->select();
		}else{
			$cats=$this->model_cate->order("listorder")->select();
		} 
		$this->assign("category",$cats);
		$this->assign("cate",$this->category);
		$this->display();
	}
	
	//添加分类
	public function cadd() {
		if(IS_POST){
			if ($this->model_cate->create()) {
				if ($this->model_cate->add()!==false) {
					$this->success("添加成功！", U("Dress/cindex"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->model_cate->getError());
			}
		}else{
			$this->assign("cate",$this->category);
			$this->display();
		}
	}
	
	//编辑分类
	function cedit(){
		if(IS_POST){
			if ($this->model_cate->create()) {
				if ($this->model_cate->save()!==false) {
					$this->success("保存成功！", U("Dress/cindex"));
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->model_cate->getError());
			}
		}else{
			$id= intval(I("get.id"));
			$cat=$this->model_cate->where("id=$id")->find();
			$this->assign($cat);
			$this->assign("cate",$this->category);
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