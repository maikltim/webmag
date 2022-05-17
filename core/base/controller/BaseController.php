<?php 

namespace core\base\controller;

use core\base\exception\RouteException;

abstract class BaseController
{

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parametrs;


    public function route() {

        $controller = str_replace('/', '\\', $this->controller);

       try {
        $object = new \ReflectionMethod($controller, 'request');

        $args = [
            'parametrs' => $this->parametrs,
            'inputMethod' => $this->inputMathod,
            'outputMethod' => $this->outputMathod
        ];

        $object->invoke(new $controller, $args);
       } catch (\ReflectionException $e) {
           throw new RouteException($e);
       }

    }

    public function request($args) {
        
    }

} 