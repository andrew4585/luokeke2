<?php
/**
 * 微信系统信息
 */
namespace Wx\Controller;
use Think\Log;
class SystemController extends IndexController {
	
	function _initialize() {
		parent::_initialize();
		if(!$this->model_config)
			$this->model_config = D("WxConfig");
	}
	
	/**
	 * 账号信息页面
	 */
	function index(){
		$info = $this->model_config->getBaseInfo();
		$this->assign("info",$info);
		$this->display();
	}
	
	/**
	 * 保存公众账号账号信息
	 */
	public function modifyBaseInfo(){
	    try{
	        $this->model_config->modify($_POST);
	        $this->success("保存成功",U("System/index"));
	    }catch (\Exception $e){
	        $this->error($e->getMessage());
	    }
	}
	
	//-------------------------------微信导航
	/**
	 * 显示菜单页面
	 */
	public function menu(){
		$model_menu = D("WxMenu");
		$list = $model_menu->getChildMenu();
		$this->assign("list",$list);
		$this->display();
	}
	/**
	 * 添加导航
	 */
	public function menuAdd(){
		$model_menu = D("WxMenu");
		if(IS_POST){
		    if($model_menu->create($_POST)){
		        $result=$model_menu->add();
		        if($result){
		            $this->success("添加成功",U("System/menu"));
		        }else{
		            $this->error("添加失败");
		        }
		    }else{
		        $this->error($model_menu->getError());
		    }
		}else{
			$first = $model_menu->where("fid = 0")->select();
			$this->assign("first",$first);
			$this->display();
		}
	}
	
	public function menuEdit(){
	   $model_menu = D("WxMenu");
	   $id = I("nav_id",0,'intval');
	   if(IS_POST){
		    if($model_menu->create($_POST)){
		        $result=$model_menu->where("nav_id=$id")->save();
		        if($result){
		            $this->success("编辑成功",U("System/menu"));
		        }else{
		            $this->error("编辑失败");
		        }
		    }else{
		        $this->error($model_menu->getError());
		    }
		}else{
			$info = $model_menu->where("nav_id=$id")->find();
			$first = $model_menu->where("fid = 0")->select();
			$this->assign($info);
			$this->assign("first",$first);
			$this->display();
		}
	}
	
	public function menuDel(){
	    $id = I("get.nav_id",0,'intval');
	    if(empty($id)){
	        $this->error("参数缺失");
	    }else{
	        $model_menu = D("WxMenu");
	        $result = $model_menu->where("nav_id = $id")->delete();
	        if($result){
	            $this->success("删除成功");
	        } else{
	            $this->error("删除失败");
	        }
	    }
	}
	
	/**
	 * 将编辑好的菜单上传到微信
	 */
	public function uploadWX(){
	    $model_nav=D("WxMenu");
	    $list=$model_nav->where("fid=0")->select();
	    $newList=array();
	    foreach($list as $item){
	        $newItem=array();
	        $child=$model_nav->where("fid=".$item['nav_id'])->select();
	        if(!empty($child)){
	            $newItem['name']=urlencode($item['name']);
	            $newItem['sub_button']=array();
	            foreach($child as $j){
	                $newJ=array();
	                $newJ['type']=$j['type'];
	                $newJ['name']=urlencode( $j['name']);
	                $key=$j['type']=='view'?'url':'key';
	                $newJ[$key]=urlencode($j['content']);
	                array_push($newItem['sub_button'],$newJ);
	            }
	        }else{
	            $newItem['type']=$item['type'];
	            $newItem['name']=urlencode($item['name']);
	            $key=$item['type']=='view'?'url':'key';
	            $newItem[$key]=urlencode($item['content']);
	        }
	        array_push($newList, $newItem);
	    }
	    $this->getThinkWechat();
	    $body['button']=$newList;
	    $body=json_encode($body);
	    $body=urldecode($body);
	    $mess_json=$this->thinkWechat->setMenu($body);
	    $mess=json_decode($mess_json,true);
	    if(0==$mess['errcode']){
	        $this->success("设置成功",U("System/navigation2"));
	    }else{
	        Log::record($mess_json);
	        $this->error($mess['errmsg']);
	    }
	}
	
	/**
	 * 排序
	 */
	public function listorders(){
		$model = D("WxMenu");
		$status = parent::_listorders($model);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
}

