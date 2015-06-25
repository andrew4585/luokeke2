<?php
namespace Home\Controller;
use Common\Controller\HomeBaseController;

class IndexController extends HomeBaseController {

    public $model_config;
    private $model_user;
    public $openid;
    public $user;
    
    public function __construct(){
        parent::__construct();
        header("Content-Type: text/html; charset=utf-8");
        $this->setModelConfig();
        $this->setModelUser();
//         $this->openid = 'ox1QntxmnsVy0UYOxIDOGUfPCgqE';
//         $this->user			= $this->model_user->where("openid='$this->openid' and is_subscribe=1")->find();
//         $this->user['score']= D("Users")->where("openid='$this->openid'")->getField("score");
//         $this->user['uid']= D("Users")->where("openid='$this->openid'")->getField("id");
        $this->Oauth();
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
        $byscore=$model_goods->field("id,post_title,post_pic,post_score")->where("status=1")->order("post_price")->select();
        // 		$byseal=$model_goods->field("goods_id,title,img,score")->order("seal_num desc")->select();
        $this->assign("byscore",$byscore);
        // 		$this->assign("byseal",$byseal);
        $this->display(":exchange");
    }
    //兑换表单
    public function exchange2(){
        $model_goods=D("Shop");
        $goods_id=I("get.goods_id");
        if(empty($goods_id))$this->layer_alert("数据丢失");
        $goods=$model_goods->field("id,post_title,post_score,post_price")->where("id=$goods_id")->find();
        if(!$goods)$this->layer_alert("获取商品信息失败");
        if($this->user['score']<$goods['post_score'])$this->layer_alert("您的积分不足，无法兑换该商品");
        $this->assign("goods",$goods);
        $this->assign("user",$this->user);
        $this->display(":exchange2");
    }
    //兑换商品详细页
    public function exchangeview(){
         $id         = I('get.goods_id',0,'intval');
        if(empty($id)) $this->layer_alert('参数丢失');
        $where      = "id=$id and status=1";
        $model_shop = D("Shop");
        $info = $model_shop->where($where)->find();
        if(!$info) $this->layer_alert('商品不存在 ！');
        $this->assign('info',$info);
        //图片信息
		$photo	= json_decode($info['smeta'],true);
		$this->assign("photo",$photo['photo']);
        $this->display(":exchangeview");
    }
    //兑换操作
    public function seal(){
        try {
            $id = I('post.id', 0, 'intval');
            if (!$id) E('参数缺失！');
            
            $data['name']=I("post.real_name");
            if(empty($data['name'])) E("请填写您的姓名");
            $data['phone']	=	I("post.tel");
            if(empty($data['phone'])) E("请填写您的电话");
            $data['address']=	I("post.address");
            if(empty($data['address'])) E("请填写您的地址");
            
            $remain = D('Shop')->where("id = $id")->getField("remain");
            if($remain<1)  E('余量不足！');
            $shop_score = I("post.post_score");
            $users = D('Users');
            if ($this->user['score'] < $shop_score)E('您积分不足！');
                
            $data['gid']       = $id;
            $data['uid']       = $this->user['uid'];
            $data['post_date'] = time();
            $data['point']     = $shop_score;
            
            $model_exchange = D("Exchange");
            $data['sumPoint'] = $this->user['score']-$shop_score;
            $result = $model_exchange->add($data);
            if ($result) {
                $rs = $users->where("id = {$this->user['uid']}")->setDec("score",$shop_score);
                $result	= D('Shop')->where("id=$id")->setDec("remain",1);
                if($result&&$rs) $this->layer_alert("提交成功!",false,U('Index/exchange'));
            } else {
                E("领取失败！");
            }
        } catch (\Exception $e) {
            $this->layer_alert($e->getMessage());
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
                if(!$this->user)	E("请微信关注我们");
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