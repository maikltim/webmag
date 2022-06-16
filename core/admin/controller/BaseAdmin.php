<?php 


namespace core\admin\controller;

use core\admin\model\Model;
use core\base\controller\BaseController;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;

abstract class BaseAdmin extends BaseController
{

    protected $model;

    protected $table;
    protected $columns;
    protected $data;
    protected $foreignData;

    protected $adminPath;


    protected $menu;
    protected $title;

    protected $translete;
    protected $blocks;

    protected $templateArr;
    protected $formTemplates;


    protected function inputData() {

        $this->init(true);
        $this->title = 'VG engine';

        if(!$this->model) $this->model = Model::instance();
        if(!$this->menu) $this->menu = Settings::get('projectTables');
        if(!$this->adminPath) $this->adminPath = PATH . Settings::get('routes')['admin']['alias'] . '/';


        if(!$this->templateArr) $this->templateArr = Settings::get('templateArr');

        $this->sendNoCasheHeaders();

    }

    protected function outputData() {


        if(!$this->content) {
            $args = func_get_arg(0);
        $vars = $args ? $args : [];

        //if(!$this->tamplate) $this->tamplate = ADMIN_TEMPLATE . 'show';

        $this->content = $this->render($this->tamplate, $vars);

        }

        $this->header = $this->render(ADMIN_TEMPLATE . 'include/header'); 
        $this->footer = $this->render(ADMIN_TEMPLATE . 'include/footer');
        
        return $this->render(ADMIN_TEMPLATE . 'layout/default');

    }


    protected function sendNoCasheHeaders() {
        header("Last-Modified: " . gmdate("D, d m Y H:i;s") . " GMT");
        header("Cach-Control: no-cache, must-revalidate");
        header("Cach-Control: max-age=0");
        header("Cach-Control: post-check=0, pre-check=0");
    }

    protected function exectBase() {
        self::inputData();
    }

    protected function createTableData($settings = false) {

        if(!$this->table) {
            if($this->parametrs) $this->table = array_keys($this->parametrs)[0];
                else {

                    if(!$settings) $settings = Settings::instance();
                    $this->table = $settings::get('defaultTable');
                }
        }

        $this->columns = $this->model->showColumns($this->table);

        if(!$this->columns) new RouteException('Не найдены поля в таблице - ' . $this->table, 2);

    }


    


    protected function expansion($args = [], $settings=false) {

        $filename = explode('_', $this->table);
        $className = '';

        foreach($filename as $item) $className .= ucfirst($item);

        if(!$settings) {
            $path = Settings::get('expansion');
        } elseif(is_object($settings)) {
            $path = Settings::get('expansion');
        } else {
            $path = $settings;
        }

        $class = $path . $className . 'Expansion';

        if(is_readable($_SERVER['DOCUMENT_ROOT'] . PATH. $class . 'php' )) {
            $class = str_replace('/', '\\', $class);

            $exp = $class::instance();

            $res = $exp->expansion($args);

            foreach($this as $name => $value) {
                $exp->$name = $name;
            }
            return  $exp->expansion($args);
        } else {
            $file = $_SERVER['DOCUMENT_ROOT'] . PATH . $path . $this->table . '.php';

            extract($args);

            if(is_readable($file)) return include $file;

        }

        return false;

    }

    protected function createOutputData($settings = false) {

        if(!$settings) $settings = Settings::instance();

        $blocks = $settings::get('blockNeedle');
        $this->translete = $settings::get('translete');

        if(!$blocks || !is_array($blocks)) {

            foreach($this->columns as $name => $item) {
                if($name === 'id_row') continue;

                if(!$this->translete[$name]) $this->translete[$name][] = $name;
                $this->blocks[0][] = $name;
            }

            return;
        }

        $default = array_keys($blocks)[0];

        foreach($this->columns as $name => $item) {
            if($name === 'id_row') continue;

            $insert = false;

            foreach($blocks as $block => $value) {
                if(!array_key_exists($block, $this->blocks)) $this->blocks[$block] = [];

                if(in_array($name, $value)) {
                    $this->blocks[$block][] = $name;
                    $insert = true;
                    break;
                }
            }

            if(!$insert) $this->blocks[$default][] = $name;
            if(!$this->translete[$name]) $this->translete[$name][] = $name;
        }

        return;

    }


    protected function createRadio($settings = false) {
        if(!$settings) $setting = Settings::instance();

        $radio = $settings::get('radio');

        if($radio) {
            foreach($this->columns as $name => $item) {
                if($radio[$name]){
                    $this->foreignData[$name] = $radio[$name];
                }
            }
        }
    
    }

}
