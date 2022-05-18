<?php 

namespace core\base\controller;

use core\base\exception\RouteException;

abstract class BaseController
{

    protected $page;
    protected $errors;

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parametrs;


    public function route() {

        $controller = str_replace('/', '\\', $this->controller);

       try {
        $object = new \ReflectionMethod($controller, 'request');

        $args = [
            'parameters' => $this->parameters,
            'inputMethod' => $this->inputMathod,
            'outputMethod' => $this->outputMathod
        ];

        $object->invoke(new $controller, $args);
       } catch (\ReflectionException $e) {
           throw new RouteException($e->getMessage());
       }

    }

    public function request($args) {
        $this->parameters = $args['parameters'];

        $inputData = $args['inputMethod'];
        $outputData = $args['outputMethod'];

        $this->$inputData();
        $this->page = $this->$outputData();

        $data = $this->$inputData();

        if(method_exists($this, $outputData)) $this->page = $this->$outputData($data);
        elseif($data) $this->page = $data;

        if($this->errors) {
            $this->writeLog();
        }
        $this->getPage();
    }

    protected function render($path = '', $parameters = []) {

        extract($parameters);

        if(!$path) {
            $path = TEMPLATE . explode('controller', strtolower((new \ReflectionClass($this))->getShortName()))[0];
        }

        ob_start();

        if(!@include_once $path . '.php') throw new RouteException('Отсутствует шаблон - '.$path);

        return ob_end_clean();

    }

    protected function getPage() {
        if(is_array($this->page)) {
            foreach($this->page as $block) echo $block;
        } else {
            echo $this->page;
        }
        exit();
    }

} 