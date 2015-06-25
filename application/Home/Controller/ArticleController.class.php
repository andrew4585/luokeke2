<?php
namespace Home\Controller;

class ArticleController extends IndexController {

    //微信js类
    public $jssdk;
    //微信帮助类
    public $thinkWechat;
    
    protected $appid;
    
    protected $appsecret;
    
    
    public function __construct(){
        parent::__construct();
    }
    
    public function index(){
        try {
            //获取文章信息
            $article_id = I("get.article_id");
            if(!$article_id) E("编号丢失");
            $model_article = D("WxArticle");
            $info = $model_article->where("id = $article_id")->find();
            
            //初始化jssdk信息
            import("Think.WX.jssdk");
            $this->jssdk= new \JSSDK($this->getAppid(), $this->getAppsecret());
            $signPackage = $this->jssdk->GetSignPackage();
            
            //关注连接
            $subscribe_url=$this->model_config->val("subscribe_url");
            $web = $this->model_config->val("web");
            $this->assign("info",$info);
            $this->assign("subscribe_url",$subscribe_url);
            $this->assign("signPackage",$signPackage);
            $this->assign("uid",$this->user['uid']);
            $this->assign("web",$web);
            $this->display("index");
        } catch (\Exception $e) {
            $this->layer_alert($e->getMessage());
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
}