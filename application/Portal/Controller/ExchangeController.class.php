<?php
namespace Portal\Controller;

use Common\Controller\MemberbaseController;

class ExchangeController extends MemberbaseController
{
    protected $model_exchange;

    function _initialize(){
        parent::_initialize();
        $this-$model_exchange=D("Exchange");
    }

    function index()
    {

    }
}