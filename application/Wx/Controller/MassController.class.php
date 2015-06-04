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

    public function checkuser()
    {
        return $res = $this->thinkWechat->check_allgroup();
    }

    public function massmessage()
    {
        $group=I("post.group");
        $sourceId = $_POST['hiddenid'];
        if(empty($group)) $this->error("请选择用户分组");
        if(!$sourceId) $this->error("素材不存在");
        $articleId = $this->modelArticle->where("cid = $sourceId")->getField('id',true);
        if(!$sourceId)$this->error("该素材没有相关文章！");
        $type = $this->modelSource->where("id=$sourceId")->getField('type');
        $users = $this->checkuser();
        Log::record($users,'WARN');
        $wxrestr = json_decode($users,true);
        Log::record($wxrestr,'WARN');
        switch($type) {
            case '0':
                $id = $articleId[0];
                $content = $this->modelArticle->where("id=$id")->getField("post_content");
                $i = $this->thinkWechat->mass_text($content, $group);
                break;
            default:
                $this->error("素材类型错误");
                break;
        }
        if($i>0){
            $this->success("群发任务提交成功");
        }else{
            $this->error("群发任务提交失败");
        }

    }
}