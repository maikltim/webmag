<?php

function debug(...$args)
{
    foreach ($args as $value) {
        echo '<pre> value => ' . print_r($value, true) . '</pre>';
    }
}


if(!function_exists('mb_str_replace')) {

    function mb_str_replace($needle, $text_replace, $haystack) {
        return implode($text_replace, explode($needle, $haystack));
    
    }

}