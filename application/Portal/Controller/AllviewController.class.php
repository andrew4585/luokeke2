<?php
namespace Portal\Controller;


class AllviewController extends IndexController {
    protected $model_allview ;
    public function __construct(){
        parent::__construct();
        $this->model_allview=D("Allview");
    }
    public function lists(){
        $this->_list($this->model_allview,true,array("post_excerpt"),array(),6,"recommended desc,listorder");
        $this->assign("ad_dress",$this->_getAd("dress"));
        $this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
        //3个摆放在一起的二级页面广告位
        $this->assign("second_page_3",$this->_getAd("second_page_3"));
        //服务承诺
        $this->assign("servePromise",$this->_getAd("servePromise"));
        //婚纱礼服广告位（专用）
        $this->assign("ad_dress",$this->_getAd("dress"));
        $this->display("list");
    }
}