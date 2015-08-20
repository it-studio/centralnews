<?php

namespace CentralNews\Model;

use CentralNews\Exception;
use CentralNews\Service\Response;

abstract class Manager
{
    /**
     * @var int 
     */
    protected $idGroup;

    /**
     * @var \CentralNews\Service\Client 
     */
    protected $centralNewsApi;

    /**
     * @param \CentralNews\Model\Client $centralNewsApi
     */
    public function __construct(Client $centralNewsApi)
    {
        $this->centralNewsApi = $centralNewsApi;
    }

    /**
     * @throws \CentralNews\Exception\DomainException
     * @return int
     */
    public function getIdGroup()
    {
        if (!$this->idGroup) {
            throw new Exception\DomainException;
        }
        return $this->idGroup;
    }

    /**
     * @param int $idGroup
     * @throws \CentralNews\Exception\InvalidArgumentException
     * @return \CentralNews\Model\Manager
     */
    public function setIdGroup($idGroup)
    {
        if (!ctype_digit($idGroup)) {
            throw new Exception\InvalidArgumentException;
        }
        $this->idGroup = $idGroup;
        return $this;
    }

    /**
     * @todo obj request
     * @return \CentralNews\Service\Response
     */
    public function sendRequest($method, $param, $x, $y, $headers)
    {
        return new Response($this->centralNewsApi->createApiClient()->call($method, $param, $x, $y, $headers));
    }

}