<?php namespace Coco\Controller;

use Common\Controller\AdminbaseController;

class CococatController extends AdminbaseController{
    protected $cococat_obj;

    function _initialize() {
        parent::_initialize();
        $this->cococat_obj = D("Common/CocoCat");
    }

    function index(){
        $cats=$this->cococat_obj->where("cat_status!=0")->select();
        $this->assign("cococat",$cats);
        $this->display();
    }

    function  add(){
        $this->display();
    }

    /**
     *  添加
     */
    public function add_post() {
        if (IS_POST) {
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
        $cococat=$this->cococat_obj->where("cid=$id")->find();
        $this->assign($cococat);
        $this->display();
    }

    function edit_post(){
        if (IS_POST) {
            if ($this->cococat_obj->create()) {
                if ($this->cococat_obj->save()!==false) {
                    $this->success("保存成功！", U("cococat/index"));
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
}