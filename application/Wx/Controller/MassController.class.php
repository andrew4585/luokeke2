<?php namespace Wx\Controller;

use Think\Log;

class MassController extends IndexController

{
    private $modelGroup;
    private $modelArticle;
    private $modelSource;
    public function __construct()
    {
        parent::__construct();
        $this->modelArticle = D('WxArticle');
        $this->modelGroup   = D('WxGroup');
        $this->modelSource  = D('WxSource');
        $this->getThinkWechat();
    }

    public function index()
    {
        $category = $this->modelGroup->getCats();
        $this->assign('category',$category);
        $this->display();
    }
    public function source()
    {
        R('Article/massSource');
        $this->display();
    }

    public function massmessage()
    {
        $group = I("post.groupid");
        $sourceId = I("post.hiddenid");
        if(empty($group)) $this->error("请选择用户分组");
        if($sourceId) $this->error("素材不存在");
        $articleId = $this->modelArticle->where("cid = $sourceId")->getField('id',true);
        if(!$sourceId)$this->error("该素材没有相关文章！");
        $type = $this->modelSource->where("id=$sopurceId")->getField('type');
        switch($type){
            case '0'://为0是文字素材
                $content = $modelArticle->where("id = $article[0]")->getField("content");
                $i = $this->thinkWechat->mass_text ( $content, $group);
                break;
            //case '3':
        }
    }
}