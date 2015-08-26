<?php

namespace CentralNews\Model;

use CentralNews\Exception;
use CentralNews\Service\Request;
use CentralNews\Service\SoapClient;

abstract class Manager
{
    /** @var \CentralNews\Service\SoapClient */
    protected $soapClient;

    /**
     * @param \CentralNews\Service\SoapClient $soapClient
     */
    public function __construct(SoapClient $soapClient)
    {
        $this->soapClient = $soapClient;
    }

    /**
     * @param \CentralNews\Service\Request
     * @return \CentralNews\Service\Response
     */
    public function sendRequest(Request $request)
    {
        return $this->soapClient->sendRequest($request);
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