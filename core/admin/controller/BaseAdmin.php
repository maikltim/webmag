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

            if($arr['fields']) {
                if(is_array($arr['fields'])) {
                    $fields = Settings::instance()->arrayMergeRecursive($fields, $arr['fields']);
                } else {
                    $fields[] = $arr['fields'];
                }
               

            }

            if($this->columns['parent_id']) {
                if(!in_array('parent_id', $fields)) $fields[] = 'parent_id';
                $order[] = 'parent_id';
            }

            if($this->columns['menu_position']) $order[] = 'menu_position';
                elseif($this->columns['date']) {
                    if($order) $order_direction = ['ASC', 'DESC'];
                        else $order_direction[] = ['DESC'];

                        $oreder[] = 'date';
                }
                if($arr['order']) {

                    if(is_array($arr['order'])) {
                        $order = Settings::instance()->arrayMergeRecursive($order, $arr['order']);
                    } else {
                        $order[] = $arr['order'];
                    }
    
                }

                if($arr['order_direction']) {

                    if(is_array(['order_direction'])) {
                        $order_direction = Settings::instance()->arrayMergeRecursive($order_direction, $arr['order_direction']);
                    } else {
                        $order_direction[] = $arr['order_direction'];
                    }
    
                }

        } else {

            if(!$arr) return $this->data = [];

            $fields = $arr['fields'];
            $order = $arr['order'];
            $order_direction = ['order_direction'];

        }

        $this->data = $this->model->get($this->table, [
            'fields' => $fields,
            'order' => $order,
            'order_direction' => $order_direction
        ]);

    }


    protected function expansion($args = []) {

        $filename = explode('_', $this->table);
        $className = '';

        foreach($filename as $item) $className .= ucfirst($item);

        $class = Settings::get('expansion') . $className . 'Expansion';

        if(is_readable($_SERVER['DOCUMENT_ROOT'] . PATH. $class . 'php' )) {
            $class = str_replace('/', '\\', $class);

            $exp = $class::instance();
        }

    }

}