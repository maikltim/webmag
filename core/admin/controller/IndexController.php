<?php 

namespace core\user\controller;

use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController {

    protected $name;


    protected function inputData() 
    {

        $db = Model::instance();

        $table = 'teachers';

        $res = $db->get($table, [
            'fields' => ['id', 'name'],
            'where' => ['fio' => 'smirnova', 'name' => 'Masha', 'surname' => 'Sergeevna'],
            'operand' => ['=', '<>'],
            'condition' => ['AND'],
            'order' => ['fio', 'name'],
            'order_direction' => ['ASC', 'DESC'],
            'limit' => '1',
            'join' => [
                'join_teble1' => [
                    'table' => 'join_teble1',
                    'fields' => ['id as j_id', 'name as j-name'],
                    'type' => 'left',
                    'where' => ['name' => 'Sacha'],
                    'operand' => ['='],
                    'condition' => ['OR'],
                    'on' => [
                        'table' => 'teachers',
                        'fields' => ['id', 'parent_id']
                    ]

                    ],
                    'join_teble2' => [
                        'table' => 'join_teble2',
                        'fields' => ['id as j_id', 'name as j-name'],
                        'type' => 'left',
                        'where' => ['name' => 'Sacha'],
                        'operand' => ['='],
                        'condition' => ['AND'],
                        'on' => [
                            'table' => 'teachers',
                            'fields' => ['id', 'parent_id']
                        ]
    
                    ]
            ] 
         ]);
       
        exit(' I am admin');
    }

    

}