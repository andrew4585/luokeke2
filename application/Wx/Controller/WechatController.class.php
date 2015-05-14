<?php
namespace Wx\Controller;
use Think\Log;
class WechatController extends IndexController{
    
    public function __construct(){
        /* 加载微信SDK */
        $this->getThinkWechat();
    }
    
    public function index() {
        /* 获取请求信息 */
        $data = $this->thinkWechat->request ();
        /* 获取回复信息 */
        $reply= $this->reply ( $data );
        if(empty($reply)){
            echo '';exit;
        }
        list ( $content, $type ) =$reply;
        /* 响应当前请求 */
        $this->thinkWechat->response ( $content, $type );
    }
    
    /**
     * 定制响应信息
     * @param array $data 接收的数据
     * @return array; 响应的数据
     */
    private function reply($data) {
    
        // 消息类型
        switch ($data ['MsgType']) {
            case 'text': // 类型是文本的
                $text=$data ['Content'];
                $reply=$this->autoReply($text,false);
                if(empty($reply)){
                    $model_system=D("SystemInfo");
                    $is_dkf=$model_system->getValue("is_dkf");
                    if(0==$is_dkf){
                        $key=$model_system->getValue("error_text");		//回复错误的关键字
                        if($key)$reply=$this->autoReply($key);
                    }elseif(1==$is_dkf){
                        $reply=array("","transfer_customer_service");
                    }
                }
                break;
            case 'event' : // 类型是事件的
                // 事件类型
                switch ($data ['Event']) {
                    case 'subscribe': 	//用户关注
                        $reply = $this->getSubscribe ( $data ['FromUserName'] );
                        break;
                    case 'unsubscribe': //取消关注
                        $this->delSubscribe( $data ['FromUserName']);
                        break;
                    case 'CLICK': 		// 点击的事件
                        $reply = $this->getTaskEvent ( $data ['EventKey'], $data ['FromUserName'] );
                        break;
                    case 'MASSSENDJOBFINISH':
                        $model_mass=D("MassResult");
                        $mass['msg_id']		=$data['MsgID'];
                        $mass['status']		=$data['Status'];
                        $mass['total_count']=$data['TotalCount'];
                        $mass['filter_count']=$data['FilterCount'];
                        $mass['sent_count']	=$data['SentCount'];
                        $mass['error_count']=$data['ErrorCount'];
                        $mass['create_time']=$data['CreateTime'];
                        $model_mass->add($mass);
                        exit;
                    default :
                        exit;
                        break;
                }
                break;
            default :
                exit;
        }
        return $reply;
    }
    /**
     * 自动回复
     * @param string $text
     * @param bool $type	是否只精确查询 ，默认只精确查询
     */
    private function autoReply($text,$type=true){
        $model_key=D("key");
        $model_art=D("Article");
        $sql="select *
        from (select k.article_id,LENGTH(k.`name`) len,k.`name`,k.type
        from lkk_key k WHERE k.`name` LIKE '%$text%') l
        order by l.len limit 0,1";
        $key=$model_key->query($sql);
        $key=$key[0];
        switch ($key['type']){
            case '文字':
                $article_id=$key['article_id'];
                $content=$model_art->where("article_id=$article_id")->getField("content");
                $reply=array($content,"text");
                break;
            case '图文':
            case '多图文':
                $article_id=$key['article_id'];
                $artArr=explode(",", $article_id);
                $article=array();
                foreach ($artArr as $item){
                    $article[]=$model_art->field("article_id,title,brief,pic,url")->where("article_id=$item")->find();
                }
                // 				$article=$model_art->field("article_id,title,brief,pic,url")->where("article_id in ($article_id)")->select();
                $reply=array($article,"news");
                break;
            default:
                break;
        }
        return $reply;
    }
    /**
     * 关注成功
     * @param string $openid 用户openid
     * @return array; 响应的数据
     */
    private function getSubscribe($openid = ''){
        $web=$this->getConfigValue("web");
        if(empty($openid))exit;
        $ch = curl_init();
        $url=$web.'index.php/Api/User/registerWeixinUser/openid/'.$openid;
        $curl_opt = array(CURLOPT_URL=>$url,CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>1,);
        curl_setopt_array($ch, $curl_opt);
        curl_exec($ch);
        curl_close($ch);
        //回复信息
        $model_system=D("SystemInfo");
        $article_id=$model_system->getValue("sub_reply");
        if($article_id){
            $reply=$this->autoReply($article_id);
            return $reply;
        }
    }
    
    /**
     * 取消关注
     * @param unknown_type $openid
     */
    private function delSubscribe($openid=''){
        $model_user=D("User");
        $model_score=D("ScoreHistory");
        $user_id=$model_user->getUserId($openid);
        $model_score->where("user_id=$user_id")->delete();
        $result=$model_user->delSubscribe($openid);
        if(!$result)exit;//修改数据失败
        echo '';exit;
    }
    /**
     * 根据article_id回复信息
     * @param string $article
     */
    private function replyByArticleId($article_id){
        $model_art=D("Article");
        $needle=strstr($article_id,",");
        if($needle){
            $artArr=explode(",", $article_id);
            $article=array();
            foreach ($artArr as $item){
                $article[]=$model_art->field("title,brief,pic,url")->where("article_id=$item")->find();
            }
            // 			$article=$model_art->field("title,brief,pic,url")->where("article_id in ($article_id)")->select();
            $reply=array($article,"news");
        }else{
            $type=$model_art->where("article_id=$article_id")->getField("type");
            if("图文"==getArtType($type)){
                $article=$model_art->where("article_id=$article_id")->select();
                $reply=array($article,"news");
            }else{
                $content=$model_art->where("article_id=$article_id")->getField("content");
                $reply=array($content,"text");
            }
        }
        return $reply;
    }
    /**
     * 任务事件
     * @param string $taskevent 事件
     * @param string $openid   	用户openid
     * @return array; 响应的数据
     */
    private function getTaskEvent($taskevent = '', $openid = '') {
        $model_user	  = D("User");
        $model_system = D("SystemInfo");
        $userid=$model_user->getUserId($openid);// 绑定的用户ID
        if ($openid && $userid > 0) {
            switch ($taskevent) {
                case 'SIGN_TODAY' : // 每日签到
                    $model_history=D("ScoreHistory");
                    $total_socre=$model_user->where("user_id=$userid")->getField("score");
                    $data['user_id'] =$userid;
                    $data['score']	 =$model_system->getValue("today_score");
                    $data['total_score']=intval($data['score'])+intval($total_socre);
                    $data['add_time']=time();
                    $data['today']	 =date("Y-m-d");
                    $where="user_id=$userid and get_type=0 and today='".$data['today']."'";
                    $isSign=$model_history->where($where)->find();
                    if($isSign){
                        $reply=array("您今天已经签到",'text');
                        return $reply;
                    }
                    $result=$model_history->add($data);
                    if($result){
                        $where="user_id=$userid";
                        $model_user->where($where)->setInc("score",$data['score']);
                        $model_user->where($where)->setInc("sign_count",1);
                        $model_user->where($where)->setField("sign_last_time",time());
                        $reply=array("签到成功",'text');
                    }else{
                        $reply=array("您今天已经签到",'text');
                    }
                    $sign_reply=$model_system->getValue("sign_reply");
                    Log::write("sign_reply:".$sign_reply);
                    if(!empty($sign_reply)){
                        list ( $content, $type ) =$reply;
                        $restr=$this->send($content,$openid,$type);
                        $reply1=$this->autoReply($sign_reply);
                        list ( $content1, $type1 ) =$reply1;
                        $restr=$this->send($content1,$openid,$type1);
                        exit;
                    }
                    break;
                default :
                    $reply=$this->autoReply($taskevent,true);
                    break;
            }
        } else {
            $reply = array ( "签到失败", 'text' );
        }
        return $reply;
    }
    
    
    /**
     * 获取微信用户基础资料
     * @param string $openid	用户openid
     * @return array; 			响应的数据
     */
    private function getuser($openid = '') {
        $weuser = $this->thinkWechat->user ( $openid );
        return $weuser;
    }
    
    
    /**
     * 主动发送消息
     * @param string $content	内容
     * @param string $openid	发送者用户名
     * @param string $type		类型
     * @return array 返回的信息
     */
    private function send($content, $openid = '', $type = 'text') {
        $restr = $this->thinkWechat->sendMsg ( $content, $openid, $type );
        return $restr;
    }
    
}