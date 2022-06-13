<?php 


namespace core\admin\controller;

use core\base\settings\Settings;


class AddController extends BaseAdmin
{

    protected function inputData() {

        if(!$this->userId) $this->exectBase();

        $this->createTableData();

        $this->createForeignData();

        $this->createOutputData();

        $this->createRadio();

    }

    protected function createForeignProperty ($arr, $rootItems) {

        if(in_array($this->table, $rootItems['tables'])) {
            $this->foreignData[$arr['COLUMN_NAME']][0]['id'] = 0;
            $this->foreignData[$arr['COLUMN_NAME']][0]['name'] = $rootItems['name'];
        }

        $columns = $this->model->showColumns($arr['REFERENCED_TABLE_NAME']);

        $name = '';

        if($columns['name']) {
            $name = 'name';
        } else {
            foreach($columns as $key => $value) {
                if(strpos($key, 'name') !== false) {
                    $name = $key . ' as name';
                }
            }

            if(!$name) $name = $columns['id_row'] . ' as name';
        }

        if($this->data) {
            if($arr['REFERENCED_TABLE_NAME'] === $this->table) {
                $where[$this->columns['id_row']] = $this->data[$this->columns['id_row']];
                $operand[] = '<>';
            }
        }

        $foreign[$arr['COLUMN_NAME']] = $this->model->get($arr['REFERENCED_TABLE_NAME'], [
            'fields' => [$arr['REFERENCED_COLUMN_NAME'] . ' as id', $name],
            'where' => $where, 
            'operand' => $operand
        ]);

        if($foreign[$arr['COLUMN_NAME']]) {
            if($this->foreignData[$arr['COLUMN_NAME']]) {
                foreach ($foreign[$arr['COLUMN_NAME']] as $value) {
                    $this->foreignData[$arr['COLUMN_NAME']][] = $value;
                }
            }else {
                $this->foreignData[$arr['COLUMN_NAME']][] = $foreign;
            }
        }

    }


    protected function createForeignData($settings = false) {

        if(!$settings) $settings = Settings::instance();

        $rootItems = $settings->get('rootItem');

        $keys = $this->model->showForeignKeys($this->table);

        if($keys) {
            foreach($keys as $item) {

                $this->createForeignProperty($item, $rootItems);
                
            }
        } elseif($this->columns['parent_id']) {

            $arr['REFERENCED_NAME'] = 'parent_id';
            $arr['REFERENCED_COLUMN_NAME'] = $this->columns['id_row'];
            $arr['REFERENCED_TABLE_NAME'] = $this->table;
            $this->createForeignProperty($arr, $rootItem);

        }
        return;
 
    }

}
