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

    protected $adminPath;


    protected $menu;
    protected $title;


    protected function inputData() {

        $this->init(true);
        $this->title = 'VG engine';

        if(!$this->model) $this->model = Model::instance();
        if(!$this->menu) $this->menu = Settings::get('projectTables');
        if(!$this->adminPath) $this->adminPath = Settings::get('routes')['admin']['alias'] . '/';

        $this->sendNoCasheHeaders();

    }

    protected function outputData() {

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

    protected function createTableData() {

        if(!$this->table) {
            if($this->parametrs) $this->table = array_keys($this->parametrs)[0];
                else$this->table = Settings::get('defaultTable');
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

            return false;
        }

    }

}