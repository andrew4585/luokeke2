<?php
namespace Portal\Controller;

class ActiveController extends IndexController {
    protected $model_active ;
    public function __construct(){
        parent::__construct();
        $this->model_active=D("Active");
    }
    public function lists(){
        $this->_list($this->model_active,true,array("post_excerpt"),array(),1,"recommended desc,listorder");
        //3个摆放在一起的二级页面广告位
        $this->assign("second_page_3",$this->_getAd("second_page_3"));
        //服务承诺
        $this->assign("servePromise",$this->_getAd("servePromise"));
        //婚纱礼服广告位（专用）
        $this->assign("ad_dress",$this->_getAd("dress"));
        $this->display("list");
    }
}