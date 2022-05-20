<?php 

namespace core\user\controller;

use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController {

    protected $name;


    protected function inputData() 
    {

        $db = Model::instance();
       
        exit(' I am admin');
    }

    

}