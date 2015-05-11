<?php

/**
 * 会员中心
 */
namespace User\Controller;

use Common\Controller\MemberbaseController;
use Portal\Controller\IndexController;

class CenterController extends MemberbaseController {
	
	protected 	$users_model;
	protected 	$exchange;
	protected 	$userid;
	protected 	$user;
	public 		$sign_point,$share_point;
	protected 	$sign_num;
	function _initialize(){
		parent::_initialize();
		$this->users_model=D("Common/Users");
		$this->exchange = D('Exchange');
	}
	function __construct(){
		parent::__construct();
		$this->userid=sp_get_current_userid();
		$this->user=$this->users_model->where(array("id"=>$this->userid))->find();
		$this->sign_num = $this->exchange->where(array("uid"=>$this->userid,"memo"=>"网站签到"))->count();
		$this->assign('signNum',$this->sign_num);
		$this->sign_point = D('Config')->val("pc_sign");
		$this->share_point = D('Config')->val('pc_share');
		$this->assign('user',$this->user);
		$this->assign("servePromise",$this->_getAd("servePromise"));
		$this->assign("home_head",$this->_getAd("banner_user"));
	}
    //会员中心type = 2 为 签到 =3 是分享
	public function index() {
		$this->assign("home_head",$this->_getAd("home_head"));
		$signPointSum = $this->exchange->where(array("uid"=>$this->userid,"type"=>2))->sum('point');
		$this->assign('signPointSum',$signPointSum);
		$sharePointSum = $this->exchange->where(array("uid"=>$this->userid,"type"=>3))->sum('point');
		$sharePointSum = empty($sharePointSum)?0:$sharePointSum;
		$this->assign('sharePointSum',$sharePointSum);
		$map['uid'] = $this->userid;
		$list = $this->exchange->relation(true)->where($map)->order('post_date DESC')->limit(10)->select();
		$this->assign('list',$list);
    	$this->display(':user_account');
    }


    public function userrule() {
		$score_model = D("Config");
		$score['pc_share'] 	= $score_model->val("pc_share");
		$score['pc_sign']	= $score_model->val("pc_sign");
		$score['share_max'] = $score_model->val('share_max');
		$this->assign("score",$score);
    	$this->display(':userrule');
    }


    public function pointlist() {
		$order = 'post_date DESC';
		$whereAnds = array("uid=$this->userid");
		if(IS_POST){
			if(!empty($_POST['control_date'])){
				$get = strtotime($_POST['control_date']);
				array_push($whereAnds,"post_date >= $get");
			}
			if(!empty($_POST['control_date2'])){
				$get = strtotime($_POST['control_date2']);
				array_push($whereAnds,"post_date <= $get");
			}
			if(!empty($_POST['TypeID'])){
				if($_POST['TypeID'] == 1){
					array_push($whereAnds,"gid = 0");
				}elseif($_POST['TypeID'] == 2){
					array_push($whereAnds,"gid > 0");
				}else{
					array_push($whereAnds,"gid >= 0");
				}
			}
		}
		$where= join(" and ", $whereAnds);

		$count=$this->exchange->where($where)->count();
		import('Page');
		$Page       = new \Page($count,8);
		$Page->SetPager('Home', '{first}{prev}&nbsp;{liststart}{list}{listend}&nbsp;{next}{last}', array("listlong" => "6", "first" => "首页", "last" => "尾页", "prev" => " < ", "next" => " > ", "list" => "*", "disabledclass" => ""));
		$list =$this->exchange ->relation(true)->where($where)
			->limit($Page->firstRow, $Page->listRows)
			->order($order)->select();
		$this->assign("list",$list);
		$this->assign('page',$Page->show("Home"));
    	$this->display(':pointlist');
    }



	//网站签到
	public function sign(){
		if(IS_AJAX){
			$message = $this->exchange->where(array("uid" => $this->userid, "memo" => "网站签到"))->order('post_date DESC')->find();
			if ($message and date('y-m-d', $message['post_date'])==date('y-m-d', time())) {
					$this->error('今天您已经签到');
			}else {
				$data['uid'] = $this->userid;
				$data['point'] = $this->sign_point;
				$data['type'] = 2;
				$data['memo'] = '网站签到';
				$data['post_date'] = time();
				$sumPoint = $this->exchange->where("uid=%d and gid=%d and post_date<%d",array($this->userid,0,$data['post_date']))->sum('point');
				$data['sumPoint'] = $sumPoint+$data['point'];
				if ($this->exchange->create($data)) {
					$score['score'] = $data['point']+$this->user['score'];
					$User = $this->users_model->where("id = $this->userid")->save($score);
					$result = $this->exchange->add(); // 写入数据到数据库
					if ($result && $User) {
						$this->success('签到成功');
					} else {
						$this->error('签到失败');
					}
				}
			}
		}else{
			alert("非法操作");
		}

	}
}
