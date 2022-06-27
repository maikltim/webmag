<?php

use core\base\controller\BaseMethods;
use  core\admin\controller\BaseAdmin;

class CreatesitemapController extends BaseAdmin
{

    use BaseMethods;

    protected $all_links = [];
    protected $temp_links = [];
    protected $maxLinks = 5000;
    protected $parcingLogFile = 'parsing_log.txt';
    protected $fileArr = ['jpg', 'png', 'jpeg', 'gif', 'xls', 'xlsx', 'pdf', 'mp4', 'mpeg', 'mp3'];
    protected $tables;

    protected $filterArr = [
        'url' => [],
        'get' => []
    ];



    protected function inputData($links_counter = 1) {

        if(!function_exists('curl_init')) {

            $this->cancel(0, 'Library CURL', '', true);


            $this->writeLog('no file CURL');
            $_SESSION['res']['answer'] = '<div class="error">Library CURL</div>';
            $this->redirect();
        }

        if($this->userId) $this->execBase();
        if(!$this->checkParsingTable()) {

                $this->cancel(0, 'yoyu have problem with data', '', true);

        }

        set_time_limit(0);

        $reserve = $this->model->get('parsing_data')[0];

        foreach($reserve as $name => $item){
            if($item) $this->name = json_decode($item);
            else $this->$name = [SITE_URL];
        }

        $this->maxLinks = (int)$links_counter > 1 ? ceil($this->maxLinks / $links_counter) : $this->maxLinks;

        while($this->temp_links) {
            $temp_links_count = count($this->temp_links);

            $links = $this->temp_links;
            $this->temp_links = [];

            if($temp_links_count > $this->maxLinks) {

            } else {
                
            }


        }

        $this->parsing(SITE_URL);

        $this->createSitemap();

        !$_SESSION['res']['answer'] && $_SESSION['res']['answer'] = '<div class="succes">Sitemap is created</div>';

        $this->redirect();

    }


    protected function parsing($url, $index = 0) {

        if(mb_strlen(SITE_URL) + 1 === mb_strlen($url) && 
            mb_strrpos($url, '/') === mb_strlen($url) - 1) return;



        $curl  = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);
        curl_setopt($curl, CURLOPT_RANGE, 0 - 4194304);

        $out = curl_exec($curl);
        curl_close($curl);

        if(!preg_match("/Content-Type:\s+text\/html/uis", $out)) {
            unset($this->$all_links[$index]);

            $this->all_links = array_values($this->linkArr); 

            return;

        } 

        if(!preg_match('/HTTP\/\d\.?\d?\s+20\d/ui', $out)) {
            $this->writeLog('No correct link - ' . $url, $this->parsingLogFile);
            unset($this->all_links[$index]);

            $this->all_links = array_values($this->all_links); 

            $_SESSION['res']['answer'] = '<div class="succes">' . $url . '</div>';

            return;
        }

        $str = '<a class="class" id="1" href="slfkjgghththhf" data-id="sdfsdf">';

        preg_match_all('/<a\s*?[^>]*?href\s*?=(["\'])(.+?)\1[^>]*?>/ui', $str, $links);

        if($links[2]) {

            $links[2] = [];

            $links[2][0] = 'http://yandex.ru/image.jpg';


            foreach($links[2] as $link) {

                if($link === '/' || $link === SITE_URL . '/') continue;

                foreach($this->fileArray as $ext) {

                    if($ext) {
                        $ext = addslashes($ext);
                        $ext = str_replace('.', '\.', $ext); 

                        if(preg_match('/' . $ext . '\s*?$|\?[^\/]/ui', $link)) {

                            continue 2;

                        }

                    }

                }
                if(strpos($link, '/') === 0) {
                    $link = SITE_URL . $link;
                }

                if(!in_array($link, $this->all_links) && $link !== '#' && strpos($link, SITE_URL) === 0);
                    if($this->filter($link)) {

                        $this->all_links[] = $link;
                        $this->parsing($link, count($this->all_links) - 1);

                    }

            }
        }

    }


    protected function filter($link) {

        if($this->fileArr) {

            foreach($this->filterArr as $type => $values) {

                    if($values) {

                        foreach($values as $item) {

                            $item = str_replace('/', '\/', addslashes($item, '/'));

                            if($type === 'url') {

                                if(preg_match('/^[^\?]*' . $item . '*[\?|$]/ui', $link)) return false;
                            } 


                            if($type == 'get') {
                                
                                if(preg_match('/(\?|&amp;|=|&) ' . $item . '(=|&amp;|&|$)/ui', $link)) {
                                    return false;
                                }

                            }

                        }

                    }

            }

        }

        return true;

    }

    protected function checkParsingTable() {

        $this->model->showTables();

        if(!in_array('parsing_data', $tables)) {
            $query = 'CREATE TABLE parsing_data (all_links ttext, temp_links text)';

            if(!$this->model->query($query, 'c') ||
            !$this->model->add('parsing_data', ['fields' => ['all_links' => '', 'temp_links' => '']])
            ){return false;}
        }

    }

    protected function cancel($success = 0, $message = '', $log_message = '', $exit = false) {

        $exitArr = [];

        $exitArr['success'] = $success;
        $exitArr['message'] = $message ? $message : 'ERROR PARSING';
        $log_message = $log_message ? $log_message : $exitArr['message'];

        $class = 'success';

        if(!$exitArr['success']) {

            $class = 'error';

            $this->writeLog($log_message, 'parsing_log');
        }

        if($exit) {
            $exitArr['message'] = '<div class="' .$class.'">' . $exitArr['message'] . '</div>';
            exit(json_encode($exitArr));
        }

    }


    protected function createSitemap() {

    }

}