<?php namespace Wx\Controller;

use Think\Log;

class MassController extends IndexController
{
    public function __construct()
    {
        parent::__construct();
    }

    function index(){
        $category = $this->group->getCats();
        $this->assign('category',$category);
        $this->display();

    }
}