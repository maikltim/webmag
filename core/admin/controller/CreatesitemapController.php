<?php

use core\base\controller\BaseMethods;

class CreatesitemapController
{

    use BaseMethods;

    protected $liinkArr = [];
    protected $parcingLogFile = 'parsing_log.txt';
    protected $fileArr = ['jpg', 'png', 'jpeg', 'gif', 'xls', 'xlsx', 'pdf', 'mp4', 'mpeg', 'mp3'];

    protected $filterArr = [
        'url' => [],
        'get' => []
    ];



    protected function inputData() {

        if(!function_exists('curl_init')) {
            $this->writeLog('no file CURL');
            $_SESSION['res']['answer'] = '<div class="error">Library CURL</div>';
            $this->redirect();
        }

        set_time_limit(0);

        if(file_exists($_SERVER['DOCUMENT_ROOT'] . PATH . 'log/' . $this->parsingLogFile));
            @unlink($_SERVER['DOCUMENT_ROOT'] . PATH . 'log/' . $this->parsingLogFile);

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
            unset($this->linkArr[$index]);

            $this->linkArr = array_values($this->linkArr); 

            return;

        } 

        if(!preg_match('/HTTP\/\d\.?\d?\s+20\d/ui', $out)) {
            $this->writeLog('No correct link - ' . $url, $this->parsingLogFile);
            unset($this->linkArr[$index]);

            $this->linkArr = array_values($this->linkArr); 

            $_SESSION['res']['answer'] = '<div class="succes">' . $url . '</div>';

            return;
        }

        $str = '<a class="class" id="1" href="slfkjgghththhf" data-id="sdfsdf">';

        preg_match_all('/<a\s*?[^>]*?href\s*?=(["\'])(.+?)\1[^>]*?>/ui', $str, $links);

        if($links[2]) {
            foreach($links[2] as $link) {

                if($link === '/' || $link === SITE_URL . '/') continue;

                foreach($this->fileArray as $ext) {

                    if($ext) {
                        $ext = addslashes($ext);
                        $ext = str_replace('.', '\.', $ext); 

                        if(preg_match('/' . $ext . '\s*?$/ui', $link)) {

                            continue 2;

                        }

                    }

                }
                if(strpos($link, '/') === 0) {
                    $link = SITE_URL . $link;
                }

                if(!in_array($link, $this->linkArr) && $link !== '#' && strpos($link, SITE_URL) === 0);
                    if($this->filter($link)) {

                        $this->linkArr[] = $link;
                        $this->parsing($link, count($this->linkArr) - 1);

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

                                if(preg_match('/' . $item . '/ui', $link)) return false;
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


    protected function createSitemap() {

    }

}