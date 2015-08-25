<?php

namespace CentralNews\Model;

use CentralNews\Exception;
use CentralNews\Service\Response;
use CentralNews\Service\Request;
use CentralNews\Service\Client;

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
     * @param \CentralNews\Service\Client $centralNewsApi
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
     * @param \CentralNews\Service\Request
     * @return \CentralNews\Service\Response
     */
    public function sendRequest(Request $request)
    {
        return new Response($this->centralNewsApi->createApiClient()->call($request->getOperation(), $request->getParams(), $request->getNamespace(), $request->getAction(), $request->getHeaders()));
    }

    /**
     * @param string $name
     * @param mixed $arguments
     */
    public function __call($name, $arguments)
    {
        if (property_exists($this, $name) && !empty($this->$name)) {
            if (is_array($this->$name)) {
                foreach ($this->$name as $key => $callback) {
                    call_user_func_array($callback, $arguments);
                }
            } else {
                call_user_func_array($this->$name, $arguments);
            }
        } else {
            throw new Exception\InvalidArgumentException;
        }
    }

}