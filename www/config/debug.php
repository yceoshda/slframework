<?php

function debug($data, $die = true)
{
    echo "<pre>";
    var_dump($data);
    if ($die) {
        die();
    }
}