<?php
namespace Home\Controller;
use Common\Controller\HomeBaseController;

class IndexController extends HomeBaseController {

    private $model_config;
    private $model_user;
    private $openid;
    private $user;
    
    public function __construct(){
        parent::__construct();
        header("Content-Type: text/html; charset=utf-8");
        $this->setModelConfig();
        $this->setModelUser();
        $this->openid = 'ox1QntxmnsVy0UYOxIDOGUfPCgqE';
        $this->user			= $this->model_user->where("openid='$this->openid' and is_subscribe=1")->find();
        $this->user['score']= D("Users")->where("openid='$this->openid'")->getField("score");
        $this->user['uid']= D("Users")->where("openid='$this->openid'")->getField("id");
//         $this->Oauth();
    }
    //用户中心
    public function user(){
        $this->assign("user",$this->user);
        $this->display(":user");
    }
    
    //积分记录
    public function record(){
        $p=I("get.p");
        $p=empty($p)?0:$p;
        $first_num=$p*10;
        $model_exchange = D("Exchange");
        $order = 'post_date DESC';
        $where = "uid = ".$this->user['uid'];
        $count=$model_exchange->where($where)->count();
        $where = "uid=".$this->user['uid'];
        $list =$model_exchange->where($where)
                                ->limit($first_num,10)
                                ->order($order)->select();
        $sscore = $model_exchange->where($where." and gid<>0")->sum("point");
        $sscore = empty($sscore)?0:$sscore;
        $this->assign("list",$list);
        $this->assign("total",$count);
        $this->assign("user",$this->user);
        $this->assign("sscore",$sscore);
        $this->assign("p",$p);
        $this->display(":record");
    }
    
    //签到记录
    public function sign_record(){
        $p=I("get.p");
        $p=empty($p)?0:$p;
        $first_num=$p*10;
        $model_score=D("Exchange");
        $where		="uid={$this->user['uid']} and type=2";
        $total		=$model_score->where($where)->count();
        $score		=$model_score->where($where)->sum("point");
        $signList	=$model_score->where($where)->order("post_date desc")->limit($first_num,10)->select();
        $score  = empty($score)?0:$score;
        $this->assign("list",$signList);
        $this->assign("score",$score);
        $this->assign("total",$total);
        $this->assign("p",$p);
        $this->display(":sign_record");
    }
    
    //分享记录
    public function share_record(){
        $p=I("get.p");
        $p=empty($p)?0:$p;
        $first_num=$p*10;
        $model_score=D("Exchange");
        $where		="uid={$this->user['uid']} and type=3";
        $total		=$model_score->where($where)->count();
        $score		=$model_score->where($where)->sum("point");
        $signList	=$model_score->where($where)->order("post_date desc")->limit($first_num,10)->select();
        $score  = empty($score)?0:$score;
        $this->assign("list",$signList);
        $this->assign("score",$score);
        $this->assign("total",$total);
        $this->assign("p",$p);
        $this->display(":sign_record");
    }
    
    //兑换中心
    public function exchange(){
        $model_goods=D("Shop");
        $byscore=$model_goods->field("id,post_title,post_pic,post_price")->where("status=1")->order("post_price")->select();
        // 		$byseal=$model_goods->field("goods_id,title,img,score")->order("seal_num desc")->select();
        $this->assign("byscore",$byscore);
        // 		$this->assign("byseal",$byseal);
        $this->display(":exchange");
    }
    //兑换表单
    public function exchange2(){
        $model_goods=D("Shop");
        $goods_id=I("get.goods_id");
        if(empty($goods_id))$this->error("数据丢失");
        $goods=$model_goods->field("id,title,score,memo")->where("goods_id=$goods_id")->find();
        if(!$goods)$this->layer_alert("获取商品信息失败");
        if($this->user['score']<$goods['score'])$this->layer_alert("您的积分不足，无法兑换该商品");
        $this->assign("goods",$goods);
        $this->assign("user",$this->user);
        $this->display();
    }
    //兑换商品详细页
    public function exchangeview(){
         $id         = I('get.goods_id',0,'intval');
        if(empty($id)) $this->error('参数丢失');
        $where      = "id=$id and status=1";
        $model_shop = D("Shop");
        $info = $model_shop->where($where)->find();
        if(!$info) $this->error('商品不存在 ！');
        $this->assign('info',$info);
        //图片信息
		$photo	= json_decode($info['smeta'],true);
		$this->assign("photo",$photo['photo']);
        $this->display(":exchangeview");
    }
    //兑换操作
    public function seal(){
        $data['goods_id']	=I("post.goods_id");
        $data['user_id']	=I("post.user_id");
        $data['title']		=I("post.title");
        $data['score']		=I("post.score");
        $data['nick_name']	=I("post.nick_name");
        foreach($data as $item){
            if(empty($item))$this->layer_alert("数据丢失");
        }
        $data['real_name']=I("post.real_name");
        if(empty($data['real_name']))$this->layer_alert("请填写您的姓名");
        $data['tel']	=	I("post.tel");
        if(empty($data['tel']))$this->layer_alert("请填写您的电话");
        $data['address']=	I("post.address");
        if(empty($data['address']))$this->layer_alert("请填写您的地址");
        $data['get_type']=I("post.get_type");
        $data['add_time']=time();
        $model_seal=D("SealHistory");
        if($this->user['score']<$data['score'])$this->layer_alert("您的积分不足");
        $data1['user_id']	=$data['user_id'];
        $data1['real_name']	=$data['real_name'];
        $data1['tel']		=$data['tel'];
        $data1['address']	=$data['address'];
        $this->model_user->save($data1);
        $model_goods=D("Goods");
        $result=$model_seal->add($data);
        if($result){
            $this->model_user->where("user_id=".$data['user_id'])->setDec("score",$data['score']);
            $model_goods->where("goods_id=".$data['goods_id'])->setInc("seal_num",1);
            $this->layer_alert("提交成功",false,U("Home/exchange"));
        }else{
            $this->layer_alert("提交失败");
        }
    }
    //完善用户信息
    public function info(){
        if(IS_GET){
            $this->assign("user",$this->user);
            $this->display();
        }elseif (IS_POST){
            $data['user_id']	=I("post.user_id");
            $data['real_name']	=I("post.real_name");
            $data['tel']		=I("post.tel");
            $data['address']	=I("post.address");
            $result=$this->model_user->save($data);
            if($result){
                $this->layer_alert("提交成功",false,U("Home/info"));
            }else{
                $this->layer_alert("提交失败");
            }
        }else{
            $this->layer_alert("未知错误");
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
                $this->user['score']= D("Users")->where("openid='$this->openid'")->getField("score");
                $this->user['uid']  = D("Users")->where("openid='$this->openid'")->getField("id");
                if(!$this->user)	E("请微信关注我们");
            }
        } catch (\Exception $e) {
            $this->layer_alert($e->getMessage());
        }
        
    }
    
    //弹出提示框
    private function layer_alert($msg,$isback=true,$url=''){
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