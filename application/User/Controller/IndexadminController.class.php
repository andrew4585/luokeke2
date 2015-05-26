<?php

/**
 * 会员
 */
namespace User\Controller;
use Common\Controller\AdminbaseController;
class IndexadminController extends AdminbaseController {
    
    /**
     * 会员列表
     */
    function index(){
    	$this->_lists();
    	$this->display(":index");
    }
    
    private  function _lists(){
        $model_user = D("Users");
        $where_ands =array("user_type=2");
        $order		=empty($_POST['order_type'])?"create_time":$_POST['order_type'];
        $order     .=" desc";
        $fields=array(
            'user_from'		=> array("field"=>'user_from','operator'=>'=','type'=>'string'),
            'user_login'   => array("field"=>"user_login","operator"=>"like",'type'=>'string'),
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
    
        $count=$model_user->where($where)->count();
    
        $page = $this->page($count, 20);
    
        $list =$model_user->alias('u')
        ->where($where)
        ->limit($page->firstRow, $page->listRows)
        ->order($order)->select();
        $this->assign("Page", $page->show('Admin'));
        $this->assign("formget",$_REQUEST);
        $this->assign("lists",$list);
    }
    
    /**
     * 用户详情
     */
    function info(){
        $id = I("get.id",0,'intval');
        if(!$id) $this->error("参数丢失");
        $model_user = D("Users");
        $info = $model_user->where("id = $id")->find();
        $this->assign($info);
        $this->display();
    }
    
    /**
     * 修改积分
     */
    function modifyScore(){
        $id = I("id",0,'intval');
        if(!$id)
            $this->error("参数丢失");
        
        $model_user = D("Users");
        
        if(IS_POST){
            try {
                $oldscore = $model_user->where("id=$id")->getField("score"); 
                
                $result = $model_user->save($_POST);
                if($result){
                    $nowsocre = $_POST['score'];
                    $data['uid']  = $id;
                    $data['type'] = $nowsocre>$oldscore?4:5;
                    $data['memo'] = $nowsocre>$oldscore
                                        ?"管理员奖励积分".($nowsocre-$oldscore)
                                        :"管理员扣除积分".($oldscore-$nowsocre);
                    $data['point']= abs($nowsocre-$oldscore);
                    $data['sumPoint'] = $nowsocre;
                    $data['post_date']= time();
                    $model_exch = D("Exchange");
                    $model_exch->add($data);
                    $this->success("修改成功!请刷新用户列表查看");
                }else{
                    $this->error("修改失败");
                }
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            
        }else{
            
            $info = $model_user->field("id,score,user_login")->where("id=$id")->find();
            $this->assign($info);
            $this->display();
        }
        
        
    }
    
    function ban(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id,"user_type"=>2))->setField('user_status','0');
    		if ($rst) {
    			$this->success("会员拉黑成功！", U("indexadmin/index"));
    		} else {
    			$this->error('会员拉黑失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
    
    function cancelban(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id,"user_type"=>2))->setField('user_status','1');
    		if ($rst) {
    			$this->success("会员启用成功！", U("indexadmin/index"));
    		} else {
    			$this->error('会员启用失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
}
