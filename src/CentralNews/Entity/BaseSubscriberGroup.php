<?php

namespace CentralNews\Entity;

use CentralNews\Exception\DomainException;
use CentralNews\Exception\InvalidArgumentException;

class BaseSubscriberGroup extends Entity implements ISubscriberGroup
{
    /** @var string */
    protected $name;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        parent::__construct($data);

        if (isset($data['name'])) {
            $this->setName($data['name']);
        }
    }

    /**
     * @throws CentralNews\Exception\DomainException   
     * @return string
     */
    public function getName()
    {
        if (!$this->name) {
            throw new DomainException;
        }
        return $this->name;
    }

    /**
     * 
     * @param string $name
     * @throws \CentralNews\Exception\InvalidArgumentException
     * @return this
     */
    public function setName($name)
    {
        if (empty($name)) {
            throw new InvalidArgumentException;
        }
        $this->name = $name;
        return $this;
    }

}