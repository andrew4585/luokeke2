<?php
namespace Admin\Controller;
use Think\Log;

use Common\Controller\AdminbaseController;
class Ad1Controller extends AdminbaseController{
	
	protected $ad1_obj;
	protected $ad1cat_obj;
	protected $site_obj;
	
	function _initialize() {
		parent::_initialize();
		$this->ad1_obj = D("Common/Ad1");
		$this->ad1cat_obj = D("Common/Ad1Cat");
		$this->site_obj = D("Common/Site");
	}
	function index(){
    	$categorys=$this->ad1cat_obj->field("cid,cat_name")->order("cid")->select();
		$list=$this->site_obj->field("id,site_name")->select();
	    $this->assign("categorys",$categorys);
		$where	= "1=1";
		$id		= 0;
		if(isset($_GET['cid']) && $_GET['cid']!=""){
			$cid=$_GET['cid'];
			$where.=" and ad_cid=$cid";
		}
		if(isset($_GET['id']) && $_GET['id']!=""){
			$id=$_GET['id'];
			$where.=" and site_cid=$id";
		}
		$this->assign("list",$list);
    	$this->assign("ad_cid",$cid);
		$this->assign("site_cid",$id);
		$count=$this->ad1_obj->where($where)->count();
		$page = $this->page($count,20);
		$slides=$this->ad1_obj->relation(true)->where($where)
							->order("listorder ASC,ad_id desc")
							->limit($page->firstRow,$page->listRows)->select();
		$this->assign('slides',$slides);
		$this->assign("page",$page->show("Admin"));
		$this->display();
	}
	function add(){
		//获取广告的分类
		$categorys=$this->ad1cat_obj->field("cid,cat_name")->where("cat_status!=0")->order("cid")->select();
		//获取分站名称
		$list=$this->site_obj->field("id,site_name")->select();
		$this->assign("list",$list);
		$this->assign("categorys",$categorys);
		$this->display();
	}

	function add_post(){
		if(IS_POST){
			if ($this->ad1_obj->create()) {
				//图片存储的文件夹
				$folder			= "ad";
				$pic			= sp_asset_relative_url($_POST['ad_pic']);
				$ad_pic		 	= str_replace("temp", $folder, $pic);
				$ad_thumb		= str_replace("temp", "temp/thumb", $pic);
				$ad_cut_pic 	= str_replace("temp", $folder."/thumb",$pic);
				rename($pic,$ad_pic);
				rename($ad_thumb,$ad_cut_pic);
				
				$_POST['ad_pic']		= $ad_pic;
				$_POST['ad_cut_pic']	= $ad_cut_pic;
				
				if ($this->ad1_obj->add($_POST)!==false) {
					$this->success("添加成功！", U("ad1/index"));
				} else {
					unlink($pic);
					unlink($ad_thumb);
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->ad1_obj->getError());
			}
		}
	}
	function edit(){
		$categorys=$this->ad1cat_obj->field("cid,cat_name")->where("cat_status!=0")->order("cid")->select();
		$list=$this->site_obj->field("id,site_name")->select();
//         dump($list);
		$id= intval(I("get.id"));
		$slide=$this->ad1_obj->where("ad_id=$id")->find();
		$this->assign($slide);
        $this->assign("list",$list);
		$this->assign("categorys",$categorys);
		$this->display();
	}
	
	function edit_post(){
		if(IS_POST){
			if ($this->ad1_obj->create()) {
				//图片存储的文件夹
				$folder				 = "ad";
				$pic			= sp_asset_relative_url($_POST['ad_pic']);
				$ad_pic		 	= str_replace("temp", $folder, $pic);
				$ad_thumb		= str_replace("temp", "temp/thumb", $pic);
				$ad_cut_pic 	= str_replace("temp", $folder."/thumb",$pic);
				if(!file_exists($ad_pic)){
					rename($pic,$ad_pic);
					$_POST['ad_pic']	= $ad_pic;
				}
				
				if(!file_exists($ad_cut_pic)){
					rename($ad_thumb,$ad_cut_pic);
					$_POST['ad_cut_pic']= $ad_cut_pic;
				}
				
				if ($this->ad1_obj->save($_POST)!==false) {
					$this->success("保存成功！", U("ad1/index"));
				} else {
					unlink($_POST['ad_pic']);
					unlink($_POST['ad_cut_pic']);
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->slide_obj->getError());
			}
				
		}
	}

	function delete(){
		if(isset($_POST['ids'])){
			$ids = implode(",", $_POST['ids']);
			$data['ad_status']=0;
			if ($this->ad1_obj->where("ad_id in ($ids)")->delete()!==false) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}else{
			$id = intval(I("get.id"));
			if ($this->ad1_obj->delete($id)!==false) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		
	}
	function toggle(){
		if(isset($_POST['ids']) && $_GET["display"]){
			$ids = implode(",", $_POST['ids']);
			$data['ad_status']=1;
			if ($this->ad1_obj->where("ad_id in ($ids)")->save($data)!==false) {
				$this->success("显示成功！");
			} else {
				$this->error("显示失败！");
			}
		}
		if(isset($_POST['ids']) && $_GET["hide"]){
			$ids = implode(",", $_POST['ids']);
			$data['ad_status']=0;
			if ($this->ad1_obj->where("ad_id in ($ids)")->save($data)!==false) {
				$this->success("隐藏成功！");
			} else {
				$this->error("隐藏失败！");
			}
		}
	}
	
	//排序
	public function listorders() {
		$status = parent::_listorders($this->ad1_obj);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	
	//修改状态
	public function status(){
		$status = parent::_status($this->ad1_obj);
		if ($status) {
			echo "success";
		} else {
			echo "fail";
		}
	}
	
}