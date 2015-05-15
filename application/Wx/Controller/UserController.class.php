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
	    
	    $this->display();
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
	        $this->success("成功初始化{$count}个用户");
	    }catch (\Exception $e){
	        $this->error($e->getMessage());
	    }
	}
	
	/**
	 * 检查是否含有应该标记为‘取消关注’的用户
	 */
	public function diffOpenid(){
	    $openidList=$this->model_user->getOpenidList();										//微信实际openid列表
	    $userList=$this->model_user->field("openid")->where("is_subscribe=1")->select();
	    $openids=array();																	//数据库存储openid列表
	    foreach ($userList as $item){
	        $openids[]=$item['openid'];
	    }
	    $common=array_intersect($openidList, $openids);										//共同的openid列表
	    $diff=array_diff($openids, $common);												//数据库中，应该标记为取消关注的openid
	    if(empty($diff)){
	        $this->success("不含有应该标记为取消关注的用户");
	    }else{
	        $count = 0 ;
	        foreach ($diff as $openid){
	            $this->model_user->where("openid = '$openid'")->setField("is_subscribe",0);
	            $count++;
	        }
	        $this->success("成功处理{$count}个用户");
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
	
	public function group(){
	    $model_group=D("Group");
	    $list=$model_group->order("id")->select();
	    $this->assign("list",$list);
	    $this->leftNav($this->nav, 2, 1);
	    $this->display();
	}
	//分组初始化，从微信获取用户分组信息
	public function group_init(){
	    $model_group=D("Group");
	    $model_group->where('id<>-1')->delete();
	    $this->getThinkWechat();
	    $json_group=$this->wechat->check_allgroup();
	    $groups=json_decode($json_group,true);
	    if(isset($groups['errcode']))
	        $this->error($groups['errcode'].":".$groups['errmsg']);
	    foreach($groups['groups'] as $item){
	        $model_group->add($item);
	    }
	    $this->success("初始化成功");
	}
	/**
	 * 添加、修改分组
	 */
	public function group_modify(){
	    $model_group = D("Group");
	    $method		 = I("get.method");
	    $data['name']= I("post.name");
	    //验证分组名称
	    if(empty($data['name']))alert("分组名称不能为空");
	    $hasName=$model_group->where("`name`='{$data['name']}'")->find();
	    if($hasName)alert("分组名称已存在");
	
	    import("@.ORG.ThinkWechat");
	    $wechat	= new ThinkWechat();
	
	    switch($method){
	        case 'add':
	            $restr=$wechat->create_group($data['name']);
	            $rearr=json_decode($restr,true);
	            if(isset($rearr['errcode']))alert("errcode：".$rearr['errcode']."\\n errmsg:".$rearr['errmsg']);
	            $data['id']=$rearr['group']['id'];
	            $data['count']=0;
	            $result=$model_group->add($data);
	            alert("创建成功",false,U("User/group"));
	            break;
	        case 'update':
	            $data['id']=I("post.id");
	            $restr=$wechat->update_group($data);
	            $rearr=json_decode($restr,true);
	            if(0==$rearr['errcode']){
	                $model_group->save($data);
	                alert("修改成功",false,U("User/group"));
	            }else{//微信修改分组失败
	                alert("errcode：".$rearr['errcode']."\n errmsg:".$rearr['errmsg']);
	            }
	            break;
	        default:
	            alert("操作错误");
	    }
	}
	/**
	 * 用户修改分组
	 */
	public function changeGroup(){
	    $model_group=D("Group");
	    $ids	= I("post.box");
	    $group	= I("get.group");
	    import("@.ORG.ThinkWechat");
	    $wechat	= new ThinkWechat();
	    foreach($ids as $id){
	        $user=$this->model_user->field("openid,groupid")->where("user_id=$id")->find();
	        $restr=$wechat->changeUserGroup($user['openid'], $group);
	        $restr=json_decode($restr,true);
	        if('ok'==$restr['errmsg']){
	            $model_group->where("id=$group")->setInc("count",1);
	            $model_group->where("id=".$user['groupid'])->setDec("count",1);
	            $this->model_user->where("user_id=$id")->setField("groupid", $group);
	        }
	    }
	}
}

