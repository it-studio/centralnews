<?php

namespace CentralNews\Service;

use CentralNews\Exception;

class Client
{
    const URL = 'url';
    const TOKEN = 'token';
    const USER = 'user';
    const PASSWORD = 'password';
    const ENCODING = 'encoding';

    /** @var \CentralNews\Model\SubscriberManager */
    protected $subscriberManager;

    /** @var \CentralNews\Model\OrderManager */
    protected $orderManager;

    /** @var array */
    protected $params;

    /** @var SoapClient */
    protected $soapClient;

    /** @var array */
    protected $headers;

    public function __construct(array $params)
    {
        $this->params = $params;

        $this->setHeaders(array(
            SoapClient::PASSWORD => $params[self::PASSWORD],
            SoapClient::TOKEN => $params[self::TOKEN],
            SoapClient::USER => $params[self::USER],
        ));

        $this->soapClient = $this->createSoapClient();
    }

    protected function createSoapClient()
    {
        $client = new SoapClient();
        $client->setUrl($this->getParam(self::URL, 'http://localhost'));
        $client->setEncoding($this->getParam(self::ENCODING, 'UTF-8'));
        $client->setHeaders($this->headers);
        return $client;
    }

    protected function getParam($name, $default = null)
    {
        return isset($this->params[$name]) ? $this->params[$name] : $default;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws Exception\InvalidArgumentException
     */
    public function __get($name)
    {
        $getter = 'get' . ucfirst($name);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        throw new Exception\InvalidArgumentException;
    }

    /**
     * @return \CentralNews\Model\SubscriberManager
     */
    public function getSubscriberManager()
    {
        if (!$this->subscriberManager) {
            $this->subscriberManager = $this->createSubscriberManager();
        }
        return $this->subscriberManager;
    }

    /**
     * @return \CentralNews\Model\SubscriberManager
     */
    protected function createSubscriberManager()
    {
        return new \CentralNews\Model\SubscriberManager($this->soapClient);
    }

    /**
     * @return \CentralNews\Model\OrderManager
     */
    public function getOrderManager()
    {
        if (!$this->orderManager) {
            $this->orderManager = $this->createOrderManager();
        }
        return $this->orderManager;
    }

    /**
     * @return \CentralNews\Model\OrderManager
     */
    protected function createOrderManager()
    {
        return new \CentralNews\Model\OrderManager($this->soapClient);
    }

    /**
     * @return \CentralNews\Entity\SubscriberGroup[]
     */
    public function getSubscribersGroups()
    {
        $manager = $this->getSubscriberManager();
        return $manager->getGroups();
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

}