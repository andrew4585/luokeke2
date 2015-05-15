<?php namespace Wx\Controller;

class ArticleController extends IndexController {

    function index(){
        $this->assign('replies',0);

        $this->display();
    }

    function add(){
        $this->assign('replies',0);
        $this->display();
    }
}

