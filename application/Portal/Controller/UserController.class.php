<?php
namespace Portal\Controller;
use Think\Log;

class UserController extends IndexController {

	
	public function __construct(){
		parent::__construct();
	}
	
	
	public function avatar(){
		if(IS_POST){
			try{
				$userid		= session("user.id");
				$file_src 	= "src.png";
				$upload		= "./data/upload/avatar/$userid";
				$filename162= $upload."_1.png";
				$filename48 = $upload."_2.png";
				$filename20 = $upload."_3.png";
					
				$src=base64_decode($_POST['pic']);
				$pic1=base64_decode($_POST['pic1']);
				$pic2=base64_decode($_POST['pic2']);
				$pic3=base64_decode($_POST['pic3']);
					
				if($src) {
					file_put_contents($file_src,$src);
				}
					
				file_put_contents($filename162,$pic1);
				file_put_contents($filename48,$pic2);
				file_put_contents($filename20,$pic3);
					
				$rs['status'] = 1;
				echo json_encode($rs);
			}catch (\Exception $e){
				Log::record("头像上传失败：".$e->getMessage());
				$rs['status'] = $e->getMessage();
				echo json_encode($rs);
			}
			
		}else{
			$this->display();
		}
		
	}
	
}