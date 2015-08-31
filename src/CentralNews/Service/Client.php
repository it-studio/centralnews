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

    /** @var \CentralNews\Model\EventManager */
    protected $eventManager;

    /** @var \CentralNews\Model\Manager */
    protected $manager;

    /** @var array */
    protected $params;

    /** @var SoapClient */
    protected $soapClient;

    /** @var array */
    protected $headers;

    /**
     * @param array $params
     */
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
     * @return \CentralNews\Model\EventManager
     */
    public function getEventManager()
    {
        if (!$this->eventManager) {
            $this->eventManager = $this->createEventManager();
        }
        return $this->eventManager;
    }

    /**
     * @return \CentralNews\Model\EventManager
     */
    protected function createEventManager()
    {
        return new \CentralNews\Model\EventManager($this->soapClient);
    }

    /**
     * @return \CentralNews\Model\Manager
     */
    public function getManager()
    {
        if (!$this->manager) {
            $this->manager = $this->createManager();
        }
        return $this->manager;
    }

    /**
     * @return \CentralNews\Model\Manager
     */
    protected function createManager()
    {
        return new \CentralNews\Model\Manager($this->soapClient);
    }

    /**
     * @param \CentralNews\Service\Request $request
     * @return \CentralNews\Service\Response
     */
    public function sendRequest(Request $request)
    {
        return $this->getManager()->sendRequest($request);
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @param string $email
     * @return bool
     */
    public static function isEmail($email)
    {
        $atom = "[-a-z0-9!#$%&'*+/=?^_`{|}~]";
        $localPart = "(?:\"(?:[ !\\x23-\\x5B\\x5D-\\x7E]*|\\\\[ -~])+\"|$atom+(?:\\.$atom+)*)";
        $alpha = "a-z\x80-\xFF";
        $domain = "[0-9$alpha](?:[-0-9$alpha]{0,61}[0-9$alpha])?";
        $topDomain = "[$alpha](?:[-0-9$alpha]{0,17}[$alpha])?";
        return (bool) preg_match("(^$localPart@(?:$domain\\.)+$topDomain\\z)i", $email);
    }

}