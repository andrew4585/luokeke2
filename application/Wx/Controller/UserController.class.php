<?php
/**
 * 微信系统信息
 */
namespace Wx\Controller;
use Think\Log;
class UserController extends IndexController {
	
    public $model_user;
	function _initialize() {
		parent::_initialize();
		if(!$this->model_config)
			$this->model_config = D("WxConfig");
		$this->model_user=D("WxUser");
	}
	/**
	 * 用户列表页
	 */
	public function index(){
	    //分组列表
	    $model_group = D("WxGroup");
	    $groups = $model_group->order("id")->select();
	    $this->assign("groups",$groups);
	    //用户列表
	    $this->_lists();
	    $this->display();
	}
	
	private  function _lists(){
	    
	    $where_ands =array("is_subscribe=1");
	    $order		=empty($_POST['order_type'])?"subscribe_time":$_POST['order_type'];
	    $order     .=" desc";
	    $fields=array(
	        'groupid'		=> array("field"=>'groupid','operator'=>'=','type'=>'int'),
	        'nick_name'   => array("field"=>"nick_name","operator"=>"like",'type'=>'string'),
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
	    	
	    $count=$this->model_user->where($where)->count();
	    	
	    $page = $this->page($count, 20);
	    	
	    $list =$this->model_user->alias('u')
	                ->field("u.*,u1.score,u1.id,g.name group_name")
	                ->join("sp_users    u1 on u.openid = u1.openid ")
	                ->join("sp_wx_group g  on g.id = u.groupid")
	                ->where($where)
            	    ->limit($page->firstRow, $page->listRows)
            	    ->order($order)->select();
	    $this->assign("Page", $page->show('Admin'));
	    $this->assign("formget",$_REQUEST);
	    $this->assign("list",$list);
	}
	
	
	
	/**
	 * 签到列表
	 */
	public function sign(){
	    $prefix=C("DB_PREFIX");
	    $model_score=D("ScoreHistory");
	    $where="get_type=0 and u.user_id<>0";
	
	    $nick_name	= I("get.nick_name");
	    if(!empty($nick_name)){
	        $where.=" and u.nick_name like '%$nick_name%'";
	        $param="&nick_name=$nick_name";
	        $this->assign("nick_name",$nick_name);
	    }
	    $openid		= I("get.openid");
	    if(!empty($openid)){
	        $where.=" and u.openid='$openid'";
	        $param="&openid=$openid";
	    }
	    $count=$model_score->alias("s")->join($prefix."user as u on s.user_id=u.user_id")->where($where)->count();
	    import("@.ORG.Page");
	    $page=new Page($count,20);
	    $page->parameter=$param;
	    $list=$model_score->alias("s")->field("s.id,u.user_id,u.nick_name,s.add_time,s.score,g.name")
	    ->join($prefix."user as u on s.user_id=u.user_id")
	    ->join($prefix."group g on g.id=u.groupid")
	    ->where($where)->order("add_time desc")
	    ->limit($page->firstRow,$page->listRows)->select();
	    $this->assign("list",$list);
	    $this->leftNav($this->nav,1,2);
	    $this->assign("page",$page->show());
	    $this->display();
	}
	/**
	 * 分享列表
	 */
	public function share(){
	    $prefix=C("DB_PREFIX");
	    $model_score=D("ScoreHistory");
	    $start_time = I("get.start_time");
	    $end_time	= I("get.end_time");
	    $groupid	= I("get.groupid");
	    $model_group= D("Group");
	    $groupList=$model_group->order("id")->select();
	    $where="get_type=1 and u.user_id<>0";
	
	    $nick_name	= I("get.nick_name");
	    if(!empty($nick_name)){
	        $where.=" and u.nick_name like '%$nick_name%'";
	        $param="&nick_name=$nick_name";
	        $this->assign("nick_name",$nick_name);
	    }
	    $openid		= I("get.openid");
	    if(!empty($openid)){
	        $where.=" and u.openid='$openid'";
	        $param="&openid=$openid";
	    }
	    if(!empty($start_time)){
	        if(!empty($end_time)){
	            if($start_time>$end_time)alert("起始时间不能超过截止时间");
	            $where.=" and s.add_time>=".strtotime($start_time." 00:00:00")." and s.add_time<=".strtotime($end_time." 23:59:59");
	            $param.="&start_time=$start_time&end_time=$end_time";
	        }else{
	            $where.=" and s.add_time>=".strtotime($start_time." 00:00:00");
	            $param.="&start_time=$start_time";
	        }
	    }else{
	        if(!empty($end_time)){
	            $where.=" and add_time<=".strtotime($end_time." 23:59:59");
	            $param.="&end_time=$end_time";
	        }
	    }
	    if((!empty($groupid)&&-1!=$groupid)||'0'===$groupid){
	        $where.=" and u.groupid=$groupid";
	        $param.="&groupid=$groupid";
	    }
	    $count=$model_score->alias("s")->join($prefix."user as u on s.user_id=u.user_id")->where($where)->count();
	    import("@.ORG.Page");
	    $page=new Page($count,20);
	    $page->parameter=$param;
	    $list=$model_score->alias("s")->field("s.id,u.user_id,u.nick_name,s.add_time,s.score,g.name")
	    ->join($prefix."user as u on s.user_id=u.user_id")
	    ->join($prefix."group g on g.id=u.groupid")
	    ->where($where)->order("add_time desc")
	    ->limit($page->firstRow,$page->listRows)->select();
	    $this->assign("list",$list);
	    $this->leftNav($this->nav,1,3);
	    $this->assign("page",$page->show());
	    $this->assign("groups",$groupList);
	    $this->display();
	}
	/**
	 * 阅读列表
	 */
	public function read(){
	    $prefix=C("DB_PREFIX");
	    $model_score=D("ScoreHistory");
	    $where="get_type=2 and u.user_id<>0";
	
	    $nick_name	= I("get.nick_name");
	    if(!empty($nick_name)){
	        $where.=" and u.nick_name like '%$nick_name%'";
	        $param="&nick_name=$nick_name";
	        $this->assign("nick_name",$nick_name);
	    }
	    $openid		= I("get.openid");
	    if(!empty($openid)){
	        $where.=" and u.openid='$openid'";
	        $param="&openid=$openid";
	    }
	    $count=$model_score->alias("s")->join($prefix."user as u on s.user_id=u.user_id")->where($where)->count();
	    import("@.ORG.Page");
	    $page=new Page($count,20);
	    $page->parameter=$param;
	    $list=$model_score->alias("s")->field("s.id,u.user_id,u.nick_name,s.add_time,s.score")
	    ->join($prefix."user as u on s.user_id=u.user_id")
	    ->where($where)->order("add_time desc")
	    ->limit($page->firstRow,$page->listRows)->select();
	    $this->assign("list",$list);
	    $this->leftNav($this->nav,1,4);
	    $this->assign("page",$page->show());
	    $this->display();
	}
	/**
	 * 用户详细信息
	 */
	public function info(){
	    $user_id=I("get.user_id");
	    if(empty($user_id))alert("数据丢失");
	    $user=$this->model_user->where("user_id=$user_id")->find();
	    $this->assign("user",$user);
	    $this->leftNav($this->nav,1,1);
	    $this->display();
	}
	
	/**
	 * 获取用户openid，初始化
	 */
	public function getOpenidList(){
	    try{
	        $this->getThinkWechat();
	        $openidList=$this->model_user->getOpenidList();
	        $count = 0;
	        foreach($openidList as $item){
	            $result = $this->model_user->subscribe($item);
	            if($result){
	                $this->model_user->set_groupid($item);
	                $count++;
	            }
	        }
	        $this->diffOpenid($openidList);
	        $this->success("成功初始化{$count}个用户");
	    }catch (\Exception $e){
	        Log::record($e->getMessage());
	        $this->error($e->getMessage());
	    }
	}
	
	/**
	 * 检查是否含有应该标记为‘取消关注’的用户
	 */
	public function diffOpenid($openidList){
	    $userList=$this->model_user->field("openid")->where("is_subscribe=1")->select();
	    $openids=array();																	//数据库存储openid列表
	    foreach ($userList as $item){
	        $openids[]=$item['openid'];
	    }
	    $common=array_intersect($openidList, $openids);										//共同的openid列表
	    $diff=array_diff($openids, $common);												//数据库中，应该标记为取消关注的openid
	    if(!empty($diff)){
	        foreach ($diff as $openid){
	            $this->model_user->where("openid = '$openid'")->setField("is_subscribe",0);
	        }
	    }
	}
	
	//修改用户积分
	public function ajax_score(){
	    $user_id=I("post.user_id");
	    $score=I("post.score");
	    $result=$this->model_user->where("user_id=$user_id")->setField("score", $score);
	    if($result>0){
	        echo 0;		//修改成功
	    }else{
	        echo 1;		//修改失败
	    }
	
	}
	/************用户分组 ******************/
	
	/**
	 * 分组列表
	 */
	public function group(){
	    $model_group=D("WxGroup");
	    $list=$model_group->order("id")->select();
	    $this->assign("list",$list);
	    $this->display();
	}
	
	/**
	 * 添加分组
	 */
	public function groupAdd(){
	    if(IS_POST){
	        $this->getThinkWechat();
	        $data['name'] = I("post.name");
	        $restr=$this->thinkWechat->create_group($data['name']);
	        $rearr=json_decode($restr,true);
	        if(isset($rearr['errcode']))
	            $this->error("errcode：".$rearr['errcode']."\\n errmsg:".$rearr['errmsg']);
	        $data['id']=$rearr['group']['id'];
	        $data['count']=0;
	        $model_group = D("WxGroup");
	        $result=$model_group->add($data);
	        $this->success("添加成功");
	    }else{
	        $this->display();
	    }
	}
	
	/**
	 * 编辑分组
	 */
	public function groupEdit(){
	    $model_group = D("WxGroup");
	    if(IS_POST){
	        $this->getThinkWechat();
	        $restr=$this->thinkWechat->update_group($_POST);
	        $rearr=json_decode($restr,true);
	        if(0==$rearr['errcode']){
	            $model_group->save($_POST);
	            $this->success("修改成功");
	        }else{//微信修改分组失败
	            $this->error("errcode：".$rearr['errcode']."\n errmsg:".$rearr['errmsg']);
	        }
	    }else{
	        $id = I("get.id",0,'intval');
	        if(empty($id))
	            $this->error("参数缺失");
	        $info = $model_group->where("id=$id")->find();
	        $this->assign($info);
	        $this->display();
	    }
	}
	//分组初始化，从微信获取用户分组信息
	public function group_init(){
	    try{
	        $model_group=D("WxGroup");
	        $model_group->where('id<>-1')->delete();
	        $this->getThinkWechat();
	        $json_group=$this->thinkWechat->check_allgroup();
	        $groups = json_decode($json_group,true);
	        if(isset($groups['errcode']))
	            $this->error($groups['errcode'].":".$groups['errmsg']);
	        foreach($groups['groups'] as $item){
	            $model_group->add($item);
	        }
	        $this->success("初始化成功");
	    }catch (\Exception $e){
	        $this->error($e->getMessage());
	    }
	}
	/**
	 * 删除分组
	 */
	public function groupDel(){
	    $data['id'] = I("get.id",0,'intval');
	    if(empty($data['id']))
	        $this->error("参数缺失");
	    $this->getThinkWechat();
	    $json_res = $this->thinkWechat->delete_group($data);
	    $res = json_decode($json_res,true);
	    if(isset($res['errcode'])){
	        $this->error($res['errcode'].":".$res['errmsg']);
	    }else{
	        $model_group = D("WxGroup");
	        $model_group->where("id={$data['id']}")->delete();
	        $this->success("删除成功");
	    }
	}
	/**
	 * 用户修改分组
	 */
	public function changeGroup(){
	    $ids	= I("post.ids");
	    $groupid	= I("post.join_group",0,"intval");
	    if(!$ids) $this->error("请选择需要修改分组的用户");
	    $model_group=D("WxGroup");
	    $model_user = D("WxUser");
	    $this->getThinkWechat();
	    //修改成功数
	    $succNo = 0;
	    foreach($ids as $id){
	        $user=$model_user->field("openid,groupid,nick_name")->where("openid='$id'")->find();
	        if($user){
	           //判断是否修改的分组与原分组相同
	           if($groupid != $user['groupid']) {
	               $restr=$this->thinkWechat->changeUserGroup($user['openid'], $groupid);
	               $restr=json_decode($restr,true);
	               if('ok'==$restr['errmsg']){
	                   $model_group->where("id=$groupid")->setInc("count",1);
	                   $model_group->where("id=".$user['groupid'])->setDec("count",1);
	                   $model_user->where("openid='$id'")->setField("groupid", $groupid);
	                   $succNo ++;
	               }
	           }
	        }
	    }
	    if($succNo>0){
	        $this->success("成功数：$succNo");
	    }else{
	        $this->error("修改失败");
	    }
	}
	
	/**
	 * 金卡会员列表
	 */
	public function gold(){
	    try {
	        $where = "1=1";
	        $parameter = "";
	        if(!empty($_GET['realname'])){
	            $where .=" and realname like '%{$_GET['realname']}%'";
	            $parameter .="/realname/".$_GET['realname'];
	        }
	        if(!empty($_GET['bride'])){
	            $where .=" and bride like '%{$_GET['bride']}%'";
	            $parameter .="/bride/".$_GET['bride'];
	        }
	        if(!empty($_GET['tel'])){
	            $where .=" and tel like '%{$_GET['tel']}%'";
	            $parameter .="/tel/".$_GET['tel'];
	        }
	        $model_gold = D("WxUserGold");
	        $goldTotal = $model_gold->where("is_pass=1")->count();
	        $count = $model_gold->where($where)->count();
	        $page = $this->page($count, 20);
	        $list = $model_gold->where($where)->order("add_time desc")->limit($page->firstRow,$page->listRows)->select();
	        $page->param = $parameter;
	        $this->assign("Page",$page->show("Admin"));
	        $this->assign("list",$list);
	        $this->assign("formget",$_GET);
	        $goldTotal = empty($goldTotal)?0:$goldTotal;
	        $this->assign("goldTotal",$goldTotal);
	        $this->display();
	    } catch (\Exception $e) {
	        $this->error($e->getMessage());
	    }
	}
	
	/**
	 * 修改金卡会员审核状态
	 */
	public function gold_user_status(){
	    try {
	        $model_gold = D("WxUserGold");
	        $result = $model_gold->where("id={$_POST['id']}")->setField("is_pass",$_POST['status']);
	        if($result){
	            $this->success("审核通过");
	        }else{
	            $this->error("系统繁忙，请稍后再试");
	        }
	    } catch (\Exception $e) {
	        $this->error($e->getMessage());
	    }
	}
	
	/**
	 * 银卡会员列表
	 */
	public function silver(){
	    try {
	        $where = "1=1";
	        $parameter = "";
	        if(!empty($_GET['gold_name'])){
	            $where .=" and gold_name like '%{$_GET['gold_name']}%'";
	            $parameter.="/gold_name/".$_GET['gold_name'];
	        }
	        if(!empty($_GET['realname'])){
	            $where .=" and realname like '%{$_GET['realname']}%'";
	            $parameter .="/realname/".$_GET['realname'];
	        }
	        if(!empty($_GET['tel'])){
	            $where .=" and tel like '%{$_GET['tel']}%'";
	            $parameter .="/tel/".$_GET['tel'];
	        }
	        $model_gold = D("WxUserSilver");
	        $count = $model_gold->where($where)->count();
	        $page = $this->page($count, 20);
	        $list = $model_gold->where($where)->order("add_time desc")->limit($page->firstRow,$page->listRows)->select();
	        $page->param = $parameter;
	        $this->assign("Page",$page->show("Admin"));
	        $this->assign("list",$list);
	        $this->assign("formget",$_GET);
	        $this->display();
	    } catch (\Exception $e) {
	        $this->error($e->getMessage());
	    }
	}
	
	/**
	 * 修改银卡会员审核状态
	 */
	public function silver_user_status(){
	    try {
	        $model_gold = D("WxUserSilver");
	        $result = $model_gold->where("id={$_POST['id']}")->setField("status",$_POST['status']);
	        if($result){
	            $this->success("修改成功");
	        }else{
	            $this->error("系统繁忙，请稍后再试");
	        }
	    } catch (\Exception $e) {
	        $this->error($e->getMessage());
	    }
	}
	
	/**
	 * 修改银卡会员返现状态
	 */
	public function fanxian_status(){
	    try {
	        $model_silver = D("WxUserSilver");
	        $result = $model_silver->where("id={$_POST['id']}")->setField("fanxian_status",$_POST['status']);
	        if($result){
	            $model_gold = D("WxUserGold");
	            $gold_id = $model_silver->where("id={$_POST['id']}")->getField("gold_id");
	            $handle = $_POST['status']==1?"setInc":"setDec";
	            $model_gold->where("id=$gold_id")->$handle("fanxian_number",1);
	            $this->success("修改成功");
	        }else{
	            $this->error("系统繁忙，请稍后再试");
	        }
	    } catch (\Exception $e) {
	        $this->error($e->getMessage());
	    }
	}
}

