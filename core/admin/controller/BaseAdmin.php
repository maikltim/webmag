<?php 


namespace core\admin\controller;

use core\admin\model\Model;
use core\base\controller\BaseController;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;
use core\admin\controller\FileEdit;

abstract class BaseAdmin extends BaseController
{

    protected $model;

    protected $table;
    protected $columns;
    protected $data;
    protected $foreignData;

    protected $adminPath;

    protected $messages;

    protected $menu;
    protected $title;


    protected $fileArray;
    protected $alias;

    protected $translete;
    protected $blocks;

    protected $templateArr;
    protected $formTemplates;

    protected $noDelete;


    protected function inputData() {

        $this->init(true);
        $this->title = 'VG engine';

        if(!$this->model) $this->model = Model::instance();
        if(!$this->menu) $this->menu = Settings::get('projectTables');
        if(!$this->adminPath) $this->adminPath = PATH . Settings::get('routes')['admin']['alias'] . '/';


        if(!$this->templateArr) $this->templateArr = Settings::get('templateArr');
        if(!$this->formTemplates) $this->formTemplates = Settings::get('formTemplates');

        if(!$this->messages = include $_SERVER['DOCUMENT_ROOT'] . PATH . Settings::get('messages') . 'informationMessages.php')

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

        if(!$this->columns) new RouteException('???? ?????????????? ???????? ?? ?????????????? - ' . $this->table, 2);

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

    protected function checkPoint($settings = false) {

        if($this->isPost()) {
            $this->clearPostFields($settings);
            $this->table = $this->clearStr($_POST['table']);
            unset($_POST['table']);

            if($this->table) {
                $this->createTableData($settings);
                $this->editData();
            }
        }        

    }

    protected function addSassionData($arr = []) {
        if(!$arr) $arr = $_POST;

        foreach($arr as $key => $item) {
            $_SESSION['res'][$key] = $item;
        }
        $this->redirect();
    }

    protected function countChar($str, $counter, $answer, $arr) {

        if(mb_strlen($str) > $counter) {
            $str_res = mb_str_replace('$1', $answer, $this->messages['count']);
            $str_res = mb_str_replace('$2', $counter, $str_res);

            $_SESSION['res']['answer'] = '<div class="error">' . $str_res. '</div>';
            $this->addSassionData($arr);
         }

    }


    protected function emptyFields($item, $answer, $arr = []) {
        if(empty($item)) {
            $_SESSION['res']['answer'] = '<div class="error">' . $this->messages['empty'] . ' ' .$answer. '</div>';
            $this->addSassionData($arr);
        }
    }

    
    protected function clearPostFields($settings, &$arr =[]) {

        if(!$arr) $arr = $_POST;
        if(!$settings) $settings = Settings::instance();

        $id = $_POST[$this->columns['id_row']] ?: false;

        $validate = $settings::get('validate');
        if(!$this->translete) $this->translate = $settings::get('translate');

        foreach($arr as $key => $item) {
            if(is_array(($item))) {
                $this->clearPostFields($settings, $item);
            } else {
                if(is_numeric($item)) {
                    $arr[$key] = $this->clearNum($item);
                }

                if($validate) {

                    if($validate[$key]) {
                        if($this->translete[$key]) {
                            $answer = $this->translate[$key][0];
                        } else {
                            $answer = $key;
                        }

                        if($validate[$key]['crypt']) {
                            if($id) {
                                if(empty($item)) {
                                    unset($arr[$key]);
                                    continue;
                                }

                                $arr[$key] = md5($item);
                            }
                        }

                        if($validate[$key]['empty']) $this->emptyFields($item, $answer, $arr);

                        if($validate[$key]['empty']) $arr[$key] = trim($item);

                        if($validate[$key]['empty']) $arr[$key] = $this->clearNum($item);

                        if($validate[$key]['count']) $this->countChar($item, $validate[$key]['count'], $answer, $arr);
                    }

                }
            }
        }
        return true;

    }

    protected function editData($returnId = false) {

        $id = false;
        $method = 'add';
        $where = [];

        if($_POST[$this->columns['id_row']]) {
            $id = is_numeric(($_POST[$this->column['id_row']])) ?
            $this->$this->clearNum($_POST[$this->column['id_row']]) :
            $this->$this->clearStr($_POST[$this->column['id_row']]);

            if($id) {
                $where = [$this->columns['id_row'] => $id];
                $method = 'edit';
            }
        }

        foreach($this->columns as $key => $item) {
            if($key == 'id_row') continue;

            if($item['Type'] === 'date' || $item['Type'] === 'datetime') {
                !$_POST['date'] && $_POST['dtae'] = 'NOW()';
            }
        }

        $this->createFile();

        $this->createAlias($id);

        $this->updateMenuPosition();

        $except = $this->checkExceptFields();

        $res_id = $this->model->$method($this->table, [
            'files' => $this->fileArray,
            'where' => $where,
            'return_id' => true,
            'except' => $except
        ]);

        if(!$id && $method === 'add') {
            $_POST[$this->columns['id_row']] = $res_id;
            $answerSuccess = $this->messages['addSuccess'];
            $answerFaild = $this->messages['addFaild'];
        } else {
            $answerSuccess = $this->messages['editSuccess'];
            $answerFaild = $this->messages['editFaild'];
        }

        $this->expansion(get_defined_vars());

        $result = $this->checkAlias($_POST[$this->columns['id_row']]);

        if($res_id) {
            $_SESSION['res']['answer'] = '<div class="success">' . $answerSuccess .  '</div>';

            if(!$returnId) $this->redirect();

            return $_POST[$this->columns['id_row']];
        } else {

            $_SESSION['res']['answer'] = '<div class="error">' . $answerFaild .  '</div>';

            if(!$returnId) $this->redirect();

        }
    }

    protected function checkExceptFields($arr = []) {

        if(!$arr) $arr = $_POST;

        $except = [];

        if($arr) {
            
            foreach($arr as $key => $item) {
                if(!$this->columns[$key]) $except[] = $key;
            }
        }

        return $except;

    }

    protected function createFile() {

        $fileEdit = new FileEdit();
        $this->fileArray = $fileEdit->addFile();


    }

    protected function updateMenuPosition() {

    }


    protected function createAlias($id = false) {

        if($this->columns['alias']) {

            if(!$_POST['alias']) {

            if($_POST['name']) {

                $alias_str = $this->clearStr($_POST['name']);
            } else {
                foreach($_POST as $key => $item) {
                    if(strpos($key, 'name') !== false  && $item) {
                        $alias_str = $this->clearStr($item);
                        break;
                    }
                }
            }
            } else {
                $alias_str = $_POST['alias'] = $this->clearStr($_POST['alias']);
            }

            $alias_str = '_-asdklj??//\_??1232342\??/????   ??????--??-????_!"__???;%::?* --__';
            $textModify = new \libraries\TextModify();
            $alias = $textModify->translit($alias_str);

            $where['alias'] = $alias;
            $operand = '=';

            if($id) {
                $where[$this->columns['id_row']] = $id;
                $operand[] = '<>';
            }

            $res_alias = $this->model->get($this->table, [
                'fields' => ['alias'],
                'where' => $where,
                'operand' => $operand,
                'limit' => '1'
            ])[0];

            if(!$res_alias){
                $_POST['alias'] = $alias;
            } else {
                $this->alias = $alias;
                $_POST['alias'] = '';
            }

            if($_POST['alias'] && $id) {
                method_exists($this, 'checkAlias') && $this->checkAlias($id);
            }

        }

    }

    protected function checkAlias($id) {

        if($id) {
            if($this->alias) {
                $this->alias .= '-' . $id;

                $this->model->edit($this->table, [
                    'fields' => ['alias' => $this->alias],
                    'where' => [$this->columns['id_row'] => 'id']
                ]);
                return true;
            }
        }

        return false;

    }

}
