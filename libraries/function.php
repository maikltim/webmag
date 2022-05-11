<?php

function debug(...$args)
{
    foreach ($args as $value) {
        echo '<pre> value => ' . print_r($value, true) . '</pre>';
    }
}