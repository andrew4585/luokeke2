<?php
namespace Home\Controller;
use Common\Controller\HomeBaseController;

class IndexController extends HomeBaseController {

    public $model_config;
    private $model_user;
    public $openid;
    public $user;
    
    //微信js类
    public $jssdk;
    //微信帮助类
    public $thinkWechat;
    
    protected $appid;
    
    protected $appsecret;
    
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
            //初始化jssdk信息
            import("Think.WX.jssdk");
            $this->jssdk= new \JSSDK($this->getAppid(), $this->getAppsecret());
            $signPackage = $this->jssdk->GetSignPackage();
            $this->assign("signPackage",$signPackage);
        }
    }
    //用户中心
    public function user(){
        $this->assign("user",$this->user);
        $model_wx_user_gold = D("WxUserGold");
        $user = $model_wx_user_gold->field("id,is_pass,tel")->where("openid='{$this->openid}' and is_pass=1")->find();
        $gold_card = 1;
        if($user) $gold_card = 2;
        //是否有金卡，1：没有，2：有
        $this->assign("gold_card",$gold_card);
        $this->assign("tel",$user['tel']);
        $this->assign("gold_id",$user['id']);
        $this->display(":user");
    }
    
    //金卡会员
    public function gold(){
        $this->assign("user",$this->user);
        $model_wx_user_gold = D("WxUserGold");
        $user = $model_wx_user_gold->field("id,is_pass,tel")->where("openid='{$this->openid}' and is_pass=1")->find();
        $gold_card = 1;
        if($user) $gold_card = 2;
        //是否有金卡，1：没有，2：有
        $this->assign("gold_card",$gold_card);
        $this->assign("tel",$user['tel']);
        $this->assign("gold_id",$user['id']);
        $this->display("/Card/user");
    }
    
    //银卡信息
    public function silver_card(){
        $where = "";
        if($_GET['tel']){
            $where = "tel = '{$_GET['tel']}'";
        }elseif($_GET['id']){
            $where = "id = {$_GET['id']}";
        }else{
            $this->layer_alert("系统繁忙，请稍后再试或联系客服");
        }
        $model_silver = D("WxUserSilver");
        $info = $model_silver->where($where)->find();
        if(!$info) $this->layer_alert("银卡会员不存在，请联系客服",false,U("Home/Index/gold"));
        $this->assign($info);
        $this->display("/Card/silver_card");
    }
    
    //领取金卡
    public function receive_gold_card(){
        try {
            $model_wx_user_gold = D("WxUserGold");
            $user = $model_wx_user_gold->field("id,is_pass")->where("openid='{$this->openid}'")->find();
            if(IS_POST){        //提交会员卡资料信息
                if($user) E("您已提交会员资料，如有问题请联系客服");
                
                if(empty($_POST['realname']))   E("请填写姓名");
                if(empty($_POST['tel']))        E("请填写手机号");
                if(empty($_POST['servername'])) E("请填写客服姓名");
                if($_POST['type']==1){
                    if(empty($_POST['zhifubao'])) E("请填写支付宝账号");
                }else{
                    if(empty($_POST['bank_card'])) E("请填写银行卡号");
                    if(empty($_POST['bank_name'])) E("请填写开户行");
                }
                $_POST['openid'] = $this->openid;
                $_POST['add_time'] = date("Y-m-d H:i:s");
                $result = $model_wx_user_gold->add($_POST);
                if($result){
                    $this->success("您的信息已提交，请等待审查");
                }else{
                    E("系统繁忙，请稍后再试或联系客服");
                }
            }else{
                
                if($user){
                    if($user['is_pass']==1){
                        $this->layer_alert("您的金卡已可以使用，请尝试刷新页面",false,U("Home/Index/gold"));
                    }else{
                        $this->layer_alert("您的会员卡资料审核中，更多请联系客服",false,U("Home/Index/gold"));
                    }
                }
                $this->assign("user",$this->user);
                $this->display("/Card/receive_gold_card");
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    //完善金卡会员信息
    public function gold_card_edit(){
        try {
            $model_gold = D("WxUserGold");
            if(IS_POST){
                if(empty($_POST['realname']))   E("请填写姓名");
                if(empty($_POST['tel']))        E("请填写手机号");
                if(empty($_POST['servername'])) E("请填写客服姓名");
                if($_POST['type']==1){
                    if(empty($_POST['zhifubao'])) E("请填写支付宝账号");
                }else{
                    if(empty($_POST['bank_card'])) E("请填写银行卡号");
                    if(empty($_POST['bank_name'])) E("请填写开户行");
                }
                $data = array();
                $result = $model_gold->where("id={$_POST['id']}")->save(array(
                    "realname"  => $_POST['realname'],
                    "tel"       => $_POST['tel'],
                    "servername"=> $_POST['servername'],
                    "zhifubao"  => $_POST['zhifubao'],
                    "bank_card" => $_POST['bank_card'],
                    "bank_name" => $_POST['bank_name']
                ));
                if($result){
                    $this->success("操作成功");
                }else{
                    E("操作失败");
                }
            }else{
                if(empty($_GET['gold_id'])){
                    $this->layer_alert("参数缺失",false,U("Home/Index/gold"));
                }
                $info = $model_gold->where("id={$_GET['gold_id']}")->find();
                $this->assign("info",$info);
                $this->display("/Card/gold_card_edit");
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    //添加银卡
    public function add_silver_card(){
        try {
            if(IS_POST){
                if(empty($_POST['realname'])){
                    E("请填写新人姓名");
                }
                if(empty($_POST['tel'])){
                    E("请填写电话号码");
                }
                if(empty($_POST['merry_date'])){
                    E("请填写预计婚期");
                }
                
                $model_gold   = D("WxUserGold");
                $model_silver = D("WxUserSilver");
                
                $gold_info = $model_gold->field("id,realname")->where("openid='{$this->openid}'")->find();
                if(!$gold_info) E("系统繁忙，请稍后再试");
                $haveTel = $model_silver->where("tel='{$_POST['tel']}'")->find();
                if($haveTel) E("该电话号码已经注册为银卡会员");
                $result = $model_silver->add(array(
                    "gold_id"   => $gold_info['id'],
                    "gold_name" => $gold_info['realname'],
                    "realname"  => $_POST['realname'],
                    "tel"       => $_POST['tel'],
                    "merry_date"=> $_POST['merry_date'],
                    "add_time"  => date("Y-m-d H:i:s"),
                ));
                if($result){
                    $model_gold->where("id={$gold_info['id']}")->setInc("silver_number",1);
                    $this->success("添加成功",U('Index/silver_card'));
                }else{
                    E("系统繁忙，请稍后再试");
                }
            }else{
                $this->assign("user",$this->user);
                $this->display("/Card/add_silver_card");
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    //银卡会员中心
    public function silver_card_list(){
        if(empty($_GET['gold_id'])) $this->layer_alert("参数丢失",false,U("Home/Index/gold"));
        $model_silver = D("WxUserSilver");
        $list = $model_silver->where("gold_id={$_GET['gold_id']}")->order("add_time desc")->select();
        $this->assign("list",$list);
        $this->display("/Card/silver_card_list");
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
        if($this->user['score']<$goods['post_score'])$this->layer_alert("您的积分不足，无法兑换该商品",false,U('Home/Index/exchangeview')."/goods_id/".$goods_id);
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
                if($result&&$rs) $this->success("提交成功!",U('Index/exchange'));
            } else {
                E("领取失败！");
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
                $fullUrl  = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
                list($a,$b) = explode("?", $fullUrl);
                header("location:".$a);
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
    
    
    /**
     * 获取微信js类
     */
    function getJssdk(){
        if(!$this->jssdk){
            import("Think.WX.jssdk");
            $this->jssdk= new \JSSDK($this->getAppid(), $this->getAppsecret());
        }
    }
    
    /**
     * 获取微信帮助类
     */
    function getThinkWechat(){
        if(!$this->thinkWechat){
            import("Think.WX.ThinkWechat");
            $this->thinkWechat = new \ThinkWechat($this->getAppid(),$this->getAppsecret());
        }
    }
    
    function getAppid(){
        if(!$this->appid)
            $this->appid = $this->getConfigValue("appid");
        return $this->appid;
    }
    
    function getAppsecret(){
        if(!$this->appsecret)
            $this->appsecret = $this->getConfigValue("appsecret");
        return $this->appsecret;
    }
    
    /**
     * 获取配置信息
     * @param string $key 键
     */
    function getConfigValue($key){
        if(!$this->model_config)
            $this->setModelConfig();
    
        return $this->model_config->val($key);
    }
    
    public function __destruct(){
        if(!$this->user)   
            cookie("weixin_user");
    }
}