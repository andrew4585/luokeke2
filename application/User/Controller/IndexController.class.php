<?php
namespace User\Controller;

use Common\Controller\MemberbaseController;


class IndexController extends MemberbaseController {

	public function index() {
		$id=sp_get_current_userid();
		$users_model=D("Common/Users");
		$user=$users_model->where(array("id"=>$id))->find();
		if(empty($user)){
			$this->error("查无此人！");
		}
		$this->assign($user);
		$this->display(':user_account');

    }
    //退出
    public function logout(){
    	$ucenter_syn=C("UCENTER_ENABLED");
    	$login_success=false;
    	if($ucenter_syn){
    		include UC_CLIENT_ROOT."client.php";
    		echo uc_user_synlogout();
    	}
    	session("user",null);//只有前台用户退出
    	redirect(__ROOT__."/");
    }
	
	public function logout2(){
    	$ucenter_syn=C("UCENTER_ENABLED");
    	$login_success=false;
    	if($ucenter_syn){
    		include UC_CLIENT_ROOT."client.php";
    		echo uc_user_synlogout();
    	}
		if(isset($_SESSION["user"])){
		$referer=$_SERVER["HTTP_REFERER"];
			session("user",null);//只有前台用户退出
			$_SESSION['login_http_referer']=$referer;
			$this->success("退出成功！",__ROOT__."/");
		}else{
			redirect(__ROOT__."/");
		}
    }

}
