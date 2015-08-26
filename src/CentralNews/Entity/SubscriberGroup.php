<?php

namespace CentralNews\Entity;

class SubscriberGroup extends BaseSubscriberGroup
{
    /** @var string */
    protected $description;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        parent::__construct($data);

        if (isset($data['description'])) {
            $this->setDescription($data['description']);
        }
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

}