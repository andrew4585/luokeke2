<?php namespace Coco\Controller;

use Common\Controller\AdminbaseController;

class CococatController extends AdminbaseController{
    protected $cococat_obj;

    function _initialize() {
        parent::_initialize();
        $this->cococat_obj = D("Common/CocoCat");
    }

    function index(){
        $count=$this->cococat_obj->where("cat_status!=0")->count();

        $page = $this->page($count, 20);
        $cats =$this->cococat_obj->where("cat_status!=0")
            ->limit($page->firstRow, $page->listRows)
            ->order($order)->select();
        $this->assign("Page", $page->show('Admin'));
        $this->assign("cococat",$cats);
        $this->display();
    }

    function add(){
        $this->display();
    }

    /**
     *  添加
     */
    public function add_post() {
        if (IS_POST) {
            $_POST['post_date'] = time();
            $_POST['start_time'] = strtotime($_POST['start_time']);
            $_POST['end_time'] = strtotime($_POST['end_time']);
            if ($this->cococat_obj->create()) {
                if ($this->cococat_obj->add()!==false) {
                    $this->success("添加成功！", U("cococat/index"));
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $this->error($this->cococat_obj->getError());
            }
        }
    }
    function edit(){
        $id= intval(I("get.id"));
        $cococat=$this->cococat_obj->where("id=$id")->find();
        $this->assign($cococat);
        $this->display();
    }

    function edit_post(){
        if (IS_POST) {
            $_POST['start_time'] = strtotime($_POST['start_time']);
            $_POST['end_time'] = strtotime($_POST['end_time']);
            if ($this->cococat_obj->create()) {
                if ($this->cococat_obj->save()!==false) {
                    $this->success("保存成功！", U("Cococat/index"));
                } else {
                    $this->error("保存失败！");
                }
            } else {
                $this->error($this->cococat_obj->getError());
            }
        }
    }

    function delete(){
        $id = intval(I("get.id"));
        if ($this->cococat_obj->delete($id)!==false) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }

    }


    //推荐操作
    function recommend(){
        //批量推荐
        /*if(isset($_POST['ids']) && $_GET["recommend"]){
            $data["recommended"]=1;
            $ids=join(",", $_POST['ids']);
            if ( $this->cococat_obj->where("id in ($ids)")->save($data)) {
                $this->success("显示成功！");
            } else {
                $this->error("显示失败！");
            }
        }
        //批量取消推荐
        if(isset($_POST['ids']) && $_GET["unrecommend"]){
            $data["recommended"]=0;
            $ids=join(",", $_POST['ids']);
            if ( $this->cococat_obj->where("id in ($ids)")->save($data)) {
                $this->success("隐藏成功！");
            } else {
                $this->error("隐藏失败！");
            }
        }*/
        //单个推荐/取消推荐
        if(isset($_GET['id'])){
            $data['recommended'] = $_GET['recommended'];
            $result=$this->cococat_obj->where(array("id" => $_GET['id']))->save($data);
            if ($result) {
                $this->success("success");
            } else {
                $this->error("fail");
            }
        }
    }

    //排序
    public function listorders() {
        $status = parent::_listorders($this->cococat_obj);
        if ($status) {
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }
}