<?php

namespace spacelife\core;

/**
* Entity
*/
class Entity
{

    public function __construct()
    {

    }

    public function __get($key)
    {
        return $this->$key;
    }
}