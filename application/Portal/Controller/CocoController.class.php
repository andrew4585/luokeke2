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
        $category = $this->model_cat->where("recommended = 1")->find();
        $this->assign('category',$category);
        $info = $this->model_coco->where("cid={$category['id']} and recommended=1")->order('post_like desc')->select();
        $count = ceil(count($info)/2);
        $this->assign('count',$count);
        $this->assign('info',$info);
        //倒计时天数
        $end_time = $category['end_time'];
        $now_time = time();
        $day = floor(($end_time-$now_time)/3600/24);
        $this->assign('day',$day);
        $this->display();
    }

}

