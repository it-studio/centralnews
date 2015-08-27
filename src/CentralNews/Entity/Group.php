<?php

namespace CentralNews\Entity;

class Group extends Entity implements ISubscriberGroup
{

    public function __construct($id)
    {
        parent::__construct(array('id' => $id));
    }

}