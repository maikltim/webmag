<?php 

namespace core\base\settings;

use core\base\controller\Singleton;

class Settings 
{

    use Singleton;

    private $routes = [
        'admin' => [
            'alias' => 'admin',
            'path' => 'core/admin/controller',
            'hrUrl' => false,
            'routes' => [
                'product' => 'goods/getGoods/sale'
            ]
            ],
            'settings' => [ 
                'path' => 'core/base/settings/'
            ],
            'plugins' => [
                'path' => 'core/plugins/',
                'hrUrl' => false,
                'dir' => false,
            ],
            'user' => [ 
                'path' => 'core/user/controller/',
                'hrUrl' => true,
                'routes' => [
                    'catalog' => 'site'
                ],
            ],
            
            'default' => [ 
                'controller' => 'IndexController',
                'inputMethod' => 'inputData',
                'outputMethod' => 'outputData'
            ]
    ];

    private $expansion = 'core/admin/expansion/';

    private $messages = 'core/base/messages/';

    private $formTemplates = PATH . 'core/admin/view/include/form_templates/';


    private $projectTables = [
        'teachers' => ['name' => 'Teachers', 'img' => 'pages.png'],
        'students' => ['name' => 'Students']
    ];


    private $templateArr = [
        'text' => ['name', 'phone', 'address'],
        'textarea' => ['content', 'keywords']
    ];

    private $translate = [
        'name' => [],
        'content' => [],
    ];

    private $radio = [
        'visible' => ['нет', 'да', 'default' => 'да']
    ];

    private $rootItems = [
        'name' => ['Корневая'],
        'tables' => ['articles']
    ];

    private $validate = [
        'name' => ['empty' => true, 'trim' => true],
        'price' => ['int' => true],
        'login' => ['empty' => true, 'trim' => true],
        'password' => ['crypt' => true, 'empty' => true],
        'keywords' => ['count' => 70, 'trim' => true],
        'description' => ['count' => 160, 'trim' => true],
    ];

    private $blockNeedle = [
        'vg-rows' => [],
        'vg_img' => [],
        'vg-content' => []
    ];
    

    static public function get($property)
    {

        return self::instance()->$property;

    }

    public function clueProperties($class)
    {

        $baseProperties = [];

        foreach($this as $name => $item) {
            $property = $class::get($name);
            
            if(is_array($property) && is_array($item)) {
                $baseProperties[$name] = $this->arrayMergeRecursive($this->$name, $property);
                continue;
            }
            if(!$property) $baseProperties[$name] = $this->$name;
        }
        return $baseProperties;
    }

    public function arrayMergeRecursive()
    {

        $arrays = func_get_args();

        $base = array_shift($arrays);

        foreach($arrays as $array) {
            foreach($array as $key => $value) {
                if(is_array($value) && is_array($base[$key])) {
                    $base[$key] = $this->arrayMergeRecursive($base[$key], $value);
                } else {
                    if(is_int($key)) {
                        if(!in_array($value, $base)) array_push($base, $value);
                        continue;
                    }
                    $base[$key] = $value;
                }
            }
        }
        return $base;

    }

}
