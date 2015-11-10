<?php
namespace Home\Controller;
use Common\Controller\HomeBaseController;

class HongBaoController extends HomeBaseController {

    public $model_config;
    private $model_user;
    public $openid;
    public $user;
    
    public function __construct(){
        parent::__construct();
        header("Content-Type: text/html; charset=utf-8");
        $this->setModelConfig();
        $this->setModelUser();
         //$this->openid = 'ox1QntxmnsVy0UYOxIDOGUfPCgqE';
        if($_GET['openid']&&IS_AJAX){
            $this->openid       = $_GET['openid'];
            $this->user			= $this->model_user->where("openid='$this->openid' and is_subscribe=1")->find();
            $this->user['score']= D("Users")->where("openid='$this->openid'")->getField("score");
            $this->user['uid']  = D("Users")->where("openid='$this->openid'")->getField("id");
        }else{
            $this->Oauth();
        }
    }
    //双十一填写手机号，领取红包
    public function double11(){
//         $this->assign("user",$this->user);
//         $model_wx_user_gold = D("WxUserGold");
//         $user = $model_wx_user_gold->field("id,is_pass")->where("openid='{$this->openid}' and is_pass=1")->find();
//         $gold_card = 1;
//         if($user) $gold_card = 2;
//         //是否有金卡，1：没有，2：有
//         $this->assign("gold_card",$gold_card);
        try {
            if(IS_POST){
                $model_hb = D("HongbaoDouble11");
                if(empty($_POST['tel'])) E("请填写手机号");
                $haveUser = $model_hb->where("openid='{$this->openid}' or tel='{$_POST['tel']}'")->find();
                if($haveUser) E("您已经领取红包");
                $result = $model_hb->add(array(
                    "openid" => $this->openid,
                    "tel"    => $_POST['tel']
                ));
                if($result){
                    $this->success("您领取的红包将于11.11日发放，请耐心等待");
                }else{
                    $this->error("系统繁忙，请稍后再试");
                }
            }else{
                $this->assign("openid",$this->openid);
                $this->display();
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    public function access_token($appid,$code,&$i=0){
        $appsecret=$this->model_config->val("appsecret");
        $appsecret=trim($appsecret);
        $appid=trim($appid);
        $url="https://api.weixin.qq.com/sns/oauth2/access_token";
        $param=array(
            "appid"=>$appid,
            "secret"=>$appsecret,
            "code"=>$code,
            "grant_type"=>"authorization_code"
        );
        $httpstr = http($url,$param);
        $harr = json_decode ( $httpstr, true );
    
        if(empty($harr['access_token'])){
            if ($i>5) E("获取access_token失败");
            $i++;
            $this->access_token($appid,$code,$i);
        }
        $this->openid = $harr['openid'];
    }
    
    /**
     * 网页授权
     */
    public function Oauth(){
        try {
            $this->user = cookie("weixin_user");
            
            if(empty($this->user)){
                $appid = $this->model_config->val("appid");
                $code			= I("get.code");
                if(empty($code)){
                    $url=getUri();
                    $url=urlencode($url);
                    $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$url&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
                    header("Location:". $url);
                    exit;
                }else{
                    $this->access_token($appid, $code);
                }
                
                $this->user			= $this->model_user->where("openid='$this->openid' and is_subscribe=1")->find();
                if(!$this->user)	E("请关注我们");
                $this->user['score']= D("Users")->where("openid='$this->openid'")->getField("score");
                $this->user['uid']  = D("Users")->where("openid='$this->openid'")->getField("id");
            }
        } catch (\Exception $e) {
            $this->layer_alert($e->getMessage());
        }
        
    }
    
    //弹出提示框
    public function layer_alert($msg,$isback=true,$url=''){
        $this->assign("msg",$msg);
        $this->assign("isback",$isback);
        $this->assign("url",$url);
        $this->display(":alert");
        exit;
    }
    
    public function setModelConfig() {
        if(!$this->model_config){
            $this->model_config = D("Wx/WxConfig");
        }
    }
    
    public function setModelUser() {
        if(!$this->model_user){
            $this->model_user = D("Wx/WxUser");
        }
    }
    
    public function __destruct(){
        if(!$this->user)   
            cookie("weixin_user");
    }
}