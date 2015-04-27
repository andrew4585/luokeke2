<?php
namespace Portal\Controller;


class AllviewController extends IndexController {
    protected $model_allview ;
    public function __construct(){
        parent::__construct();
        $this->model_allview=D("Allview");
    }
    public function lists(){
        $this->_list($this->model_allview,true,array("post_date,post_excerpt"),array(),6,"recommended desc,listorder");
        $this->assign("ad_dress",$this->_getAd("dress"));
        $this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
        //banner
        $this->assign("home_head",$this->_getAd("banner_view"));
        //3个摆放在一起的二级页面广告位
        $this->assign("second_page_3",$this->_getAd("second_page_3"));
        //服务承诺
        $this->assign("servePromise",$this->_getAd("servePromise"));
        //婚纱礼服广告位（专用）
        $this->assign("ad_dress",$this->_getAd("dress"));
        $this->display("list");
    }

    public function nav_index(){
        $m 			= M('Allview');
        $msg 		= $m->where('status=1 and recommended=1')->select();
        $item = array();
        foreach ($msg as $key=>$value){
            $item[] = array(
                "label" => "{$value['post_title']}",
                "href" => "{$value['url']}"
            );
        }
        $nav_arr['name'] 	= "全景看店";
        $nav_arr['name_url'] = U("Portal/Allview/lists");
        $nav_arr['items'] 	= $item;
        exit(json_encode($nav_arr));

    }
}