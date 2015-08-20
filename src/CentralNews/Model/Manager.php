<?php

namespace CentralNews\Model;

class Manager
{
    protected $idGroup = 0;

    public function getIdGroup()
    {
        if(!$this->idGroup) {
            throw new \Exception(gettext("není zvolena skupina odběratelů"));
        }
        return $this->idGroup;
    }

    public function setIdGroup($idGroup)
    {
        $this->idGroup = $idGroup;
        return $this;
    }

}