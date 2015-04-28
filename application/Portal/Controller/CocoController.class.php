<?php namespace Portal\Controller;

class CocoController extends IndexController {

    protected $model_cat;
    protected $model_coco ;

    public function __construct(){
        parent::__construct();
        $this->model_coco = D('Enroll');
        $this->model_cat 	 = D('CocoCat');
    }
    public function index(){
        //dump($_SESSION['user']);
        $category = $this->model_cat->where("recommended = 1")->find();
        if(empty($category)){
        	$this->error("活动尚未开始，敬请期待");
        }
        $this->assign('category',$category);
        $info = $this->model_coco->where("cid={$category['id']} and recommended=1")->order('post_like desc')->select();
        $count = ceil(count($info)/2);
        $this->assign('count',$count);
        $this->assign('info',$info);
        //倒计时天数
        $end_time = $category['end_time'];
        $now_time = time();
        $day = floor(($end_time-$now_time)/3600/24);
        $url = $this->_getUri();
        $this->assign('url',$url);
        $this->assign('day',$day);
        $this->assign("model_table","Enroll");
        $this->display();
    }

    public function like(){
        if(IS_AJAX){
            try{
                $this->check_login();
                //喜欢的成员编号
                $id	= I("post.id",0,'intval');
                if(empty($id)) throw new Exception("成员编号丢失");
                $userid = $_SESSION['user']['id'];
                $uservote = M('vote');
                $res = $uservote->where("enroll_id = $id and userid = $userid")->find();
                if($res){
                    $this->error(1);	//您已经投过票了!
                }else{
                    $result	= $this->model_coco->where("id=$id")->setInc("post_like",1);
                    $data['enroll_id']  = $id;
                    $data['userid']     = $userid;
                    $data['time']       = time();
                    $voteres = $uservote->data($data)->add();
                    if($result && $voteres){
                    	$this->success('操作成功');
                    }else{
                        $this->error('操作失败');
                    }
                }



            }catch (\Exception $e){
                $this->error($e->getMessage());
            }
        }else{
            alert("非法操作");
        }
    }

    public function nav_index(){
        $nav_arr['name'] 	= "可可之星";
        $nav_arr['name_url'] = U("Portal/coco");
        exit(json_encode($nav_arr));
    }
}

