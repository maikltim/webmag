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


    protected $menu;
    protected $title;


    protected function inputData() {

        $this->init(true);
        $this->title = 'VG engine';

        if(!$this->model) $this->model = Model::instance();
        if(!$this->menu) $this->menu = Settings::get('projectTables');

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


    protected function createData($arr =[], $add = true) {

        $fields = [];
        $order = [];
        $order_direction = [];

        if($add) {

            if($this->columns['id_row']) return $this->data = [];

            $fields[] = $this->columns['id_row'] . ' as id';
            if($this->columns['name']) $fields['name'] = 'name';
            if($this->columns['img']) $fields['img'] = 'img';

            if(count($fields) < 3) {
                foreach($this->columns as $key => $item) {
                    if(!$fields['name'] && strpos($key, 'name') !== false) {
                        $fields['name'] = $key . ' as name';
                    }
                    if(!$fields['name'] && strpos($key, 'img') === 0) {
                        $fields['name'] = $key . ' as name';
                    }
                }
            }

        } else {


        }

    }

}