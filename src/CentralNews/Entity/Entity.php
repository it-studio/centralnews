<?php

namespace CentralNews\Entity;

abstract class Entity
{
    /** @var int */
    protected $id;

    /**
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data = array())
    {
        if (isset($data['id'])) {
            $id = (int) $data['id'];
            if (!$id) {
                throw new \InvalidArgumentException;
            }
            $this->id = $data['id'];
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

}