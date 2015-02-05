<?php

namespace spacelife\entity;

use spacelife\core\Entity;

class UserEntity extends Entity
{

    /**
    *   getUCLogin
    *
    **/
    public function getUCLogin()
    {
        return strtoupper($this->login);
    }


}