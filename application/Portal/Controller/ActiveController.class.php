<?php
namespace Portal\Controller;

class ActiveController extends IndexController {
    protected $model_active ;
    public function __construct(){
        parent::__construct();
        $this->model_active=D("Active");
    }
    public function lists(){
        $this->_list($this->model_active,true,array("post_excerpt"),array(),6,"recommended desc,listorder");
        //3个摆放在一起的二级页面广告位
        $this->assign("second_page_3",$this->_getAd("second_page_3"));
        //服务承诺
        $this->assign("servePromise",$this->_getAd("servePromise"));
        //婚纱礼服广告位（专用）
        $this->assign("ad_dress",$this->_getAd("dress"));
        $this->display("list");
    }
    
    public function info(){
    	$id = I("get.id",0,'intval');
    	if(empty($id)){
    		$where = "status = 1";
    	}else{
    		$where = "id=$id and status=1";
    	}
    	$smeta = $this->model_active->where($where)->getField("smeta");
    	$smeta	= json_decode($smeta,true);
    	$photo	= $smeta['photo'];
    	$newPhoto = array();
    	foreach ($photo as $item){
    		array_push($newPhoto, "'".__ROOT__."/{$item['url']}'");
    	}
    	$photoStr = join(",", $newPhoto);
    	$this->assign("photo",$photoStr);
    	$this->assign("desc_beautiful",$this->_getAd("desc_beautiful"));
    	//3个摆放在一起的二级页面广告位
    	$this->assign("second_page_3",$this->_getAd("second_page_3"));
    	//服务承诺
    	$this->assign("servePromise",$this->_getAd("servePromise"));
    	$this->display(":Single:single1");
    }

	public function nav_index(){
		$m 			= M('Active');
		$msg 		= $m->where('status=1 and recommended=1')->select();
		$item = array();
		foreach ($msg as $key=>$value){
			$item[] = array(
				"label" => "{$value['post_title']}",
				"href" => U("Portal/Active/info/id/{$value['id']}")
			);
		}
		$nav_arr['name'] 	= "最新活动";
		$nav_arr['name_url'] = U("Portal/active/lists");
		$nav_arr['items'] 	= $item;
		exit(json_encode($nav_arr));

	}
}