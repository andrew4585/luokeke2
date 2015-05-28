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
                    $model_config=D("WxConfig");
                    $is_dkf=$model_config->val("is_dkf");
                    if(0==$is_dkf){
                        $key=$model_config->val("error_text");		//回复错误的关键字
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
                        $model_mass=D("WxMass");
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
        $model_key=D("WxKeyword");
        $model_art=D("WxSource");
        $model_article = D("WxArticle");
        $sql="select *
        from (select k.sourceid,LENGTH(k.`post_title`) len,k.`post_title`,k.type
        from sp_wx_keyword k WHERE k.`post_title` LIKE '%$text%') l
        order by l.len limit 0,1";
        $key=$model_key->query($sql);
        $key=$key[0];
        switch ($key['type']){
            case 0:
                $sourceId=$key['sourceid'];
                $content=$model_article->where("cid = $sourceId")->getField("post_content");
                $reply=array($content,"text");
                break;
            case 3:
                $sourceId=$key['sourceid'];
                $artArr = $model_article->where("cid = $sourceId")->getField("id",true);
                $article=array();
                foreach ($artArr as $item){
                    $article[]=$model_article->field("id,post_title,post_excerpt,post_pic,post_url")->where("id=$item")->find();
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
        //$web=$this->getConfigValue("web");
        if(empty($openid))exit;
        $model_user = D("WxUser");
        $model_user->subscribe($openid);
		$model_group=D("WxGroup");
		$model_group->where("id=0")->setInc("count",1);
        $model_Config=D("WxConfig");
        $article_id=$model_Config->val("sub_reply");
        if($article_id){
             return $reply=$this->autoReply($article_id);
         }
    }
    
    /**
     * 取消关注
     * @param unknown_type $openid
     */
    private function delSubscribe($openid=''){
        $model_user=D("WxUser");
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
        $model_user	  = D("Users");
        $model_system = D("WxConfig");
        $userid=$model_user->getUserId($openid);// 绑定的用户ID
        if ($openid && $userid > 0) {
            switch ($taskevent) {
                case 'SIGN_TODAY' : // 每日签到
                    $exchange = D('Exchange');
                    $res = $model_user -> isSign($userid,$exchange);
                    if(!$res){
                        return $reply = array("您今天已经签到",'text');
                    }else {
                        $data['uid'] = $userid;
                        $data['point'] = 10;
                        $data['type'] = 2;
                        $data['memo'] = '微信签到';
                        $data['post_date'] = time();
                        Log::write('data',$data);
                        $sumPoint = $exchange->where("uid=%d and gid=%d and post_date<%d", array($userid, 0, $data['post_date']))->sum('point');
                        $data['sumPoint'] = (int)$sumPoint + $data['point'];
                        $result = $model_user->sign($userid, $data);
                        if ($result) {
                            $where = "id=$userid";
                            $model_user->where($where)->setInc("score", $data['point']);
                            $reply = array("签到成功", 'text');
                        }
                        $sign_reply = $model_system->val("sign_reply");
                        if (!empty($sign_reply)) {
                            list ($content, $type) = $reply;
                            $restr = $this->send($content, $openid, $type);
                            $reply1 = $this->autoReply($sign_reply);
                            list ($content1, $type1) = $reply1;
                            $restr = $this->send($content1, $openid, $type1);
                            exit;
                        }
                    }
                    /*}
                    $model_user ->sign($userid,$exchange);
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
                    }*/
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