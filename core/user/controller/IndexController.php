<?php 

namespace core\user\controller;

use core\base\controller\BaseController;

class IndexController extends BaseController {

    protected $name;


    protected function inputData() 
    {

        $this->name = 'Masha';

    }

    protected function outputData() 
    {

        $vars = func_get_arg(0);
        exit($this->render('', $vars));

    }

}