<?php

namespace CentralNews\Entity;

use CentralNews\Exception\DomainException;
use CentralNews\Exception\InvalidArgumentException;

class SubscriberGroup extends Entity
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $description;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        parent::__construct($data);

        if (isset($data['name'])) {
            $this->setName($data['name']);
        }

        if (isset($data['description'])) {
            $this->setDescription($data['description']);
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * 
     * @param string $name
     * @throws \CentralNews\Exception\InvalidArgumentException
     * @return this
     */
    public function setName($name)
    {
        $name = (string) $name;
        if (empty($name)) {
            throw new InvalidArgumentException;
        }
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

}