<?php namespace Wx\Controller;

class ArticleController extends IndexController {

    protected $model_cate;
    protected $model_obj;
    protected $imgFolder;
    protected $model_article;
    function _initialize() {
        parent::_initialize();
        $this->imgFolder	= "Article";
        $this->model_cate	= D("WxSourceCat");
        $this->model_article= D('WxArticle');
        $this->model_obj	= D("WxSource");
    }
    //公共参数
    function commonParam(){
        //分类列表
        $cats = $this->getCategory($this->model_cate);
        $this->assign("cats",$cats);
    }

    function index(){
        //$data = $this->model_obj->relation(true)->select();
        $this->_lists();
        $type = array(0=>'文字',3=>'多图文');
        $this->assign('type',$type);
    		$this->commonParam();
    		$this->display();
    }
    //外部群发引用的方法
    public function massSource()
    {
        $this->_lists();
        $type = array(0=>'文字',3=>'多图文');
        $this->assign('type',$type);
        $this->commonParam();
    }

    private  function _lists(){
      //status=1,表示文章未删除，0表示文章已删除
      $where_ands =array("status=1");
      //istop:首页置顶，recommended：推荐，listorder：排序，post_date:发布时间
      $order		="listorder ASC,post_date DESC";
      $fields=array(
          'cid'		=> array("field"=>'cid','operator'=>'=','type'=>'int'),
          'start_time'=> array("field"=>"post_date","operator"=>">=",'type'=>'time'),
          'end_time'  => array("field"=>"post_date","operator"=>"<=",'type'=>'time'),
          'keyword'   => array("field"=>"post_title","operator"=>"like",'type'=>'string'),
      );
      foreach ($fields as $param =>$val){
        if (!empty($_REQUEST[$param])) {

          $operator=$val['operator'];		//操作
          $type	 =$val['type'];			//参数类型
          $field   =$val['field'];		//字段名
          $get	 =$_REQUEST[$param];	//数据
          if($operator=="like"){
            $get="'%$get%'";
          }elseif ($type=='time'){
            $get=strtotime($get);
          }elseif ($type=='string'){
            $get="'$get'";
          }
          array_push($where_ands, "$field $operator $get");
        }
      }
      $where= join(" and ", $where_ands);

      $count=$this->model_obj->where($where)->count();

      $page = $this->page($count, 20);

      $list =$this->model_obj ->relation(true)->where($where)
      ->limit($page->firstRow, $page->listRows)
      ->order($order)->select();
      $this->assign("Page", $page->show('Admin'));
      $this->assign("formget",$_REQUEST);
      $this->assign("list",$list);
    }

    function add(){
        if(IS_POST){
            if($this->model_obj->create()){
                $_POST['post_date'] = time();
                $result = $this->model_obj->add($_POST);
                if($result){
                    if($_POST['type'] == 3){
                      $this->redirect("Wx/Article/addArticle/uid/$result");
                    }else{
                      $this->redirect("Wx/Article/addWords/uid/$result");
                    }
                }
            }
        }else{
            $this->commonParam();
            $this->display();
        }
    }
    //增加文字素材
    function addWords(){
      if(I('get.uid')){
          if(IS_POST){
              $_POST['cid'] = I('get.uid');
              $_POST['post_date'] = time();
              $result = $this->model_article->add($_POST);
              if ($result) {
                $this->success("添加成功！");
              } else {
                $this->error("添加失败！");
              }
          }else{
              $uid = I('get.uid');
              $this->assign('uid',$uid);
              $this->display('addWords');
          }
      }else{
          $this->error('参数不存在',U("Article/add"));
      }
    }
    //增加图文素材
    function addArticle (){
        if(I('get.uid')){
            if(IS_POST){
                foreach ($_POST as $key => $value) {
                  $value['cid'] = I('get.uid');
                  $value['post_date'] = time();
                  $value['post_content'] = htmlspecialchars($value['post_content']);
                  $value['post_pic'] = $this->removeUploadImage($this->imgFolder, $value['post_pic']);
                  $dataList[] = $value;
                }
                foreach ($dataList as $key => $value) {
                  $result = $this->model_article->add($value);
                }
                if ($result) {
          				$this->success("添加成功！");
          			} else {
          				$this->error("添加失败！");
          			}
            }else{
                $uid = I('get.uid');
                $this->assign('uid',$uid);
                $this->display('addArticle');
            }
        }else{
            $this->error('参数不存在',U("Article/add"));
        }
    }

    //编辑
    function edit($id){
        //获取类型进行定向
        $type = $this->model_obj->where("id=$id")->getField('type');
        if($type==3){
            $this->redirect("Wx/Article/editArticle/id/$id");
        }else{
            $this->redirect("Wx/Article/editWords/id/$id");
        }
    }
    function editArticle($id){
        if(IS_POST){

            $data['post_title']         = $_POST['post_title'];
            $data['cid']                = $_POST['cid'];
            $data['post_date']          = time();
            $res = $this->model_obj->where(array('id'=>$id))->save($data);
            $reply = $this->model_article->where("cid=$id")->getField('id',true);
            $replies = array_flip($reply);
            $replyids = array_keys($replies);
            foreach ($_POST as $key=>$value){
                if(count($value)>1){
                    $value['cid'] = I('get.id');
                    $value['post_date'] = time();
                    $value['post_content'] = htmlspecialchars($value['post_content']);
                    $value['post_pic'] = $this->removeUploadImage($this->imgFolder, $value['post_pic']);
                    if(in_array((int)$value['id'], $replyids)){
                       $result = $this->model_article->where("id={$value['id']}")->save($value);
                    }else{
                        $this->model_article->add($value);
                    }
                    unset($replies[$value['id']]);
                }
            }
           if (!empty($replies)) {
                $replies = array_keys($replies);
                $replies = implode(',', $replies);
                $this->model_article->where('id in('.$replies.')')->delete();
            }
            if($res||$result){
                $this->success('更新成功',U('Article/index'));
            }else{
                $this->error('更新失败');
            }
        }else{
            $info = $this->model_obj->relation(true)->find($id);
            $this->assign('info',$info);
            $this->commonParam();
            $this->display();
        }

    }
    function editWords($id){
        if(IS_POST){
            $data['post_title']         = $_POST['source_post_title'];
            $data['cid']                = $_POST['cid'];
            $data['post_date']          = time();
            $wordsData['post_title']    = $_POST['words_post_title'];
            $wordsData['post_content']  = $_POST['post_content'];
            $result = $this->model_obj->where(array('id'=>$id))->save($data);
            $res    = $this->model_article->where(array('id'=>$_POST['aid']))->save($wordsData);
            if($result || $res){
                $this->success('更新成功',U('Article/index'));
            }else{
                $this->error('更新失败');
            }
        }else{
            $info = $this->model_obj->relation(true)->find($id);
            $this->assign('info',$info);
            $this->commonParam();
            $this->display();
        }


    }

    function delete(){
      if(isset($_GET['id'])){
  			$id = intval(I("get.id"));
  			$data['status']=0;
  			if ($this->model_obj->where("id=$id")->save($data)) {
  				$this->success("删除成功！");
  			} else {
  				$this->error("删除失败！");
  			}
  		}
  		if(isset($_POST['ids'])){
  			$ids=join(",",$_POST['ids']);
  			$data['status']=0;
  			if ($this->model_obj->where("id in ($ids)")->save($data)) {
  				$this->success("删除成功！");
  			} else {
  				$this->error("删除失败！");
  			}
  		}
  	}
    public function listorders() {
  		$status = parent::_listorders($this->model_obj);
  		if ($status) {
  			$this->success("排序更新成功！");
  		} else {
  			$this->error("排序更新失败！");
  		}
  	}
    /**素材分类**/
    function cindex(){
      $cats=$this->model_cate->order("listorder")->select();
  		$this->assign("category",$cats);
  		$this->display();
    }

    function cadd(){
        if(IS_POST){
            if($_POST['post_title'] == ""){
                $this->error('添加失败');
            }
            if ($this->model_cate->create()) {
                if ($this->model_cate->add($_POST)) {
                    $this->success("添加成功！", U("Article/cindex"));
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $this->error($this->model_cate->getError());
            }
        }else{
            $this->display();
        }
    }
    //删除分类
    function cdelete(){
        $id = intval(I("get.id"));
        if ($this->model_cate->delete($id)) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }

    //编辑分类
    function cedit(){
        if(IS_POST){
            if($_POST['cat_name'] == ""){
                $this->error('更新失败');
            }
            if ($this->model_cate->create()) {
                if ($this->model_cate->save($_POST)) {
                    $this->success("保存成功！", U("Article/cindex"));
                } else {
                    $this->error("保存失败！");
                }
            } else {
                $this->error($this->model_cate->getError());
            }
        }else{
            $id= intval(I("get.id"));
            $ad1cat=$this->model_cate->where("id=$id")->find();
            $this->assign($ad1cat);
            $this->display();
        }

    }

    //分类排序
    function clistorders(){
        $status = parent::_listorders($this->model_cate);
        if ($status) {
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }
}
