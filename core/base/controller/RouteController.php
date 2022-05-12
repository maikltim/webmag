<?php 

namespace core\base\controller;

use core\base\settings\Settings;
use core\base\settings\ShopSettings;

class RouteController 
{

static private $_instance;

protected $routes;

protected $controller;
protected $inputMethod;
protected $outputMethod;
protected $parametrs;


private function __clone()
{
    
}

static public function getInstance() {
    if(self::$_instance instanceof self) {
        return self::$_instance;
    }

    return self::$_instance = new self;
}


private function __construct()
{
    
   $adress_str = $_SERVER['REQUEST_URI'];
   if(strrpos($adress_str, '/') === strlen($adress_str) - 1 && strrpos($adress_str, '/') !== 0) {
       // $this->redirect(rtrim($adress_str, '/'), 301);
   }

   $path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php')); // watch

    exit();
}

}