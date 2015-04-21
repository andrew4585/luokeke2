<?php
namespace Portal\Controller;

class IntegralController extends IndexController
{

    public function __construct ()
    {
        parent::__construct();
        $this->assign('bannerpic',1);
    }
    public function index ()
    {
        $this->assign("servePromise",$this->_getAd("servePromise"));
        $this->display();
    }
    public function info ()
    {
        $this->assign("servePromise",$this->_getAd("servePromise"));
        $this->display();
    }
}