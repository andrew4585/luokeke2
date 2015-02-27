<?php
/**
 * navication(菜单管理)
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class Nav1Controller extends AdminbaseController {
	
	
	protected $nav;
	protected $navcat;
	
	function _initialize() {
		parent::_initialize();
		$this->nav = D("Common/Nav");
		$this->navcat =D("Common/Site");
	}
	
	
	/**
	 *  显示菜单
	 */
	public function index() {
		
		if(empty($_REQUEST['cid'])){
			$cid=session("nav_site_id");
			if(empty($cid)){
				$navcat=$this->navcat->order("listorder")->find();
				$cid=$navcat['id'];
			}
		}else{
			$cid=$_REQUEST['cid'];
		}
		session("nav_site_id",$cid);
		$result = $this->nav->where("cid=$cid")->order(array("listorder" => "ASC"))->select();
		import("Tree");
		$tree = new \Tree();
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		foreach ($result as $r) {
			$r['str_manage'] = '<a href="' . U("nav1/add", array("parentid" => $r['id'],"cid"=>$r['cid'])) . '">添加子菜单</a> | <a href="' . U("nav1/edit", array("id" => $r['id'],"parentid"=>$r['parentid'],"cid"=>$r['cid'])) . '">修改</a> | <a class="J_ajax_del" href="' . U("nav1/delete", array("id" => $r['id'])) . '">删除</a> ';
			$r['status'] = $r['status'] ? "显示" : "隐藏";
			$array[] = $r;
		}
	
		$tree->init($array);
		$str = "<tr>
				<td><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input input-order'></td>
				<td>\$id</td>
				<td >\$spacer\$label</td>
				<td>\$english</td>
				<td>\$str_manage</td>
			</tr>";
		$categorys = $tree->get_tree(0, $str);
		$this->assign("categorys", $categorys);
		
		$cats=$this->navcat->order("listorder")->select();
		$this->assign("navcats",$cats);
		$this->assign("navcid",$cid);
		
		$this->display();
	}
	
	/**
	 *  添加
	 */
	public function add() {
		$cid = $_REQUEST['cid']? $_REQUEST['cid'] :session("nav_site_id");
		$result = $this->nav->where("cid=$cid")->order(array("listorder" => "ASC"))->select();
		$tree = new \Tree();
		$tree->icon = array('&nbsp;│ ', '&nbsp;├─ ', '&nbsp;└─ ');
		$tree->nbsp = '&nbsp;';
		$parentid=I("get.parentid");
		
		foreach ($result as $r) {
			$r['str_manage'] = '<a href="' . U("Menu/add", array("parentid" => $r['id'], "menuid" => $_GET['menuid'])) . '">添加子菜单</a> | <a href="' . U("Menu/edit", array("id" => $r['id'], "menuid" => $_GET['menuid'])) . '">修改</a> | <a class="J_ajax_del" href="' . U("Menu/delete", array("id" => $r['id'], "menuid" => I("get.menuid"))) . '">删除</a> ';
			$r['status'] = $r['status'] ? "显示" : "隐藏";
			$r['selected'] = $r['id']==$parentid?"selected":"";
			$array[] = $r;
		}
		$tree->init($array);
		
		$str = "<tr>
				<td><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input'></td>
				<td>\$id</td>
				<td >\$spacer\$label</td>
			    <td>\$status</td>
				<td>\$str_manage</td>
			</tr>";
		$str="<option value='\$id' \$selected>\$spacer\$label</option>";
		$nav_trees = $tree->get_tree(0, $str);
		$this->assign("nav_trees", $nav_trees);
			
			
		$cats=$this->navcat->select();
		$this->assign("navcats",$cats);
		$this->assign('navs', $this->_select());
		$this->assign("navcid",$cid);
		$this->display();
	}
	
	/**
	 *  添加
	 */
	public function add_post() {
		if (IS_POST) {
			$data=I("post.");
			$data['href']=htmlspecialchars_decode($data['href']);
			if ($this->nav->create($data)) {
				$result=$this->nav->add();
				if ($result!==false) {
					$parentid = $_POST['parentid']==0?"0":$_POST['parentid'];
					if(empty($parentid)){
						$data['path']="0-$result";
					}else{
						$parent=$this->nav->where("id=$parentid")->find();
						$data['path']=$parent[path]."-$result";
					}
					$data['id']=$result;
					session("nav_site_id",$data['cid']);
					$this->nav->save($data);
					$this->success("添加成功！", U("nav1/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->nav->getError());
			}
		}
	}
	
	
	
	/**
	 *  编辑
	 */
	public function edit() {
		$cid=intval($_REQUEST['cid']);
		$id=intval(I("get.id"));
		$result = $this->nav->where("cid=$cid and id!=$id")->order(array("listorder" => "ASC"))->select();
		import("Tree");
		$tree = new \Tree();
		$tree->icon = array('&nbsp;│ ', '&nbsp;├─ ', '&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$parentid= I("get.parentid");
		foreach ($result as $r) {
			$r['str_manage'] = '';
			$r['status'] = $r['status'] ? "显示" : "隐藏";
			$r['selected'] = $r['id']==$parentid?"selected":"";
			$array[] = $r;
		}
		
		$tree->init($array);
		$str = "<tr>
				<td><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input'></td>
				<td>\$id</td>
				<td >\$spacer\$label</td>
			    <td>\$status</td>
				<td>\$str_manage</td>
			</tr>";
		$str="<option value='\$id' \$selected>\$spacer\$label</option>";
		$nav_trees = $tree->get_tree(0, $str);
		$this->assign("nav_trees", $nav_trees);
		
		
		$cats=$this->navcat->select();
		$this->assign("navcats",$cats);
			
		$nav=$this->nav->where("id=$id")->find();
		$nav['hrefold'] = $nav['href'];
			
		$this->assign($nav);
		$this->assign('navs', $this->_select());
		$this->assign("navcid",$cid);
		$this->display();
	}
	
	/**
	 *  编辑
	 */
	public function edit_post() {
		
		if (IS_POST) {
			$parentid=empty($_POST['parentid'])?"0":$_POST['parentid'];
			if(empty($parentid)){
				$_POST['path']="0-".$_POST['id'];
			}else{
				$parent=$this->nav->where("id=$parentid")->find();
					
				$_POST['path']=$parent['path']."-".$_POST['id'];
			}
			
			$data=I("post.");
			$data['href']=htmlspecialchars_decode($data['href']);
			if ($this->nav->create($data)) {
				if ($this->nav->save() !== false) {
					$this->success("保存成功！", U("Nav1/edit",array("id"=>$data['id'])));
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->nav->getError());
			}
		}
	}
	
	/**
	 * 排序
	 */
	public function listorders() {
		$status = parent::_listorders($this->nav);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	
	/**
	 *  删除
	 */
	public function delete() {
		$id = intval(I("get.id"));;
		$count = $this->nav->where(array("parentid" => $id))->count();
		if ($count > 0) {
			$this->error("该菜单下还有子菜单，无法删除！");
		}
		if ($this->nav->delete($id)!==false) {
			$this->success("删除菜单成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
	/**
	 * select nav
	 */
	private function _select(){
		$apps=sp_scan_dir(SPAPP."*");
		$host=(is_ssl() ? 'https' : 'http')."://".$_SERVER['HTTP_HOST'].":".$_SERVER['SERVER_PORT'];
		$navs=array();
		foreach ($apps as $a){
			if(is_dir(SPAPP.$a)){
				if(!(strpos($a, ".") === 0)){
					$navfile=SPAPP.$a."/nav.php"; 	
					$app=$a;
					if(file_exists($navfile)){
						$navgeturls=include $navfile;
						foreach ($navgeturls as $key=>$url){
							$nav= http($host.U("$app/$url"));
							$nav=json_decode($nav,true);
							$navs[$key]=$nav;
						}
					}
					
				}
			}
		}
		return $navs;
	}
	//-------------------------------电梯菜单 start------------------------------------
	//显示
	public function dianti(){
		$this->nav = D("NavDianti");
		if(empty($_REQUEST['cid'])){
			$cid=session("nav_site_id");
			if(empty($cid)){
				$navcat=$this->navcat->order("listorder")->find();
				$cid=$navcat['id'];
			}
		}else{
			$cid=$_REQUEST['cid'];
		}
		session("nav_site_id",$cid);
		$result = $this->nav->where("cid=$cid")->order(array("listorder" => "ASC"))->select();
		
		$cats=$this->navcat->order("listorder")->select();
		$this->assign("list",$result);
		$this->assign("navcats",$cats);
		$this->assign("navcid",$cid);
		
		$this->display();
	}
	//添加
	public function dianti_add(){
		$this->nav = D("NavDianti");
		if(IS_POST){
			$data=I("post.");
			$data['href']=htmlspecialchars_decode($data['href']);
			if ($this->nav->create($data)) {
				$result=$this->nav->add();
				if ($result) {
					session("nav_site_id",$data['cid']);
					$this->success("添加成功！", U("Nav1/dianti"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->nav->getError());
			}
		}else{
			$cid = $_REQUEST['cid']? $_REQUEST['cid'] :session("nav_site_id");
			$cats=$this->navcat->select();
			$this->assign("navcats",$cats);
			$this->assign('navs', $this->_select());
			$this->assign("navcid",$cid);
			$this->display();
		}
	}
	//编辑
	public function dianti_edit(){
		$this->nav = D("NavDianti");
		if(IS_POST){
			$data=I("post.");
			$data['href']=htmlspecialchars_decode($data['href']);
			if ($this->nav->create($data)) {
				if ($this->nav->save() !== false) {
					$this->success("保存成功！", U("Nav1/dianti_edit",array("id"=>$data['id'])));
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->nav->getError());
			}
		}else{
			$cid = $_REQUEST['cid']? $_REQUEST['cid'] :session("nav_site_id");
			$id=intval(I("get.id"));
			
			$cats=$this->navcat->select();
			$this->assign("navcats",$cats);
				
			$nav=$this->nav->where("id=$id")->find();
			$nav['hrefold'] =$nav['href'];
				
			$this->assign($nav);
			$this->assign('navs', $this->_select());
			$this->assign("navcid",$cid);
			$this->display();
		}
		
	}
	//删除
	public function dianti_delete(){
		$this->nav = D("NavDianti");
		$id = intval(I("get.id"));;
		if ($this->nav->delete($id)!==false) {
			$this->success("删除菜单成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
	//排序
	public function dianti_listorders() {
		$this->nav = D("NavDianti");
		$status = parent::_listorders($this->nav);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	//-------------------------------电梯菜单 end--------------------------------------
}