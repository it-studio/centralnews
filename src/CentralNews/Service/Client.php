<?php

namespace CentralNews\Service;

use CentralNews\Exception;

class Client
{
    protected $encoding = 'UTF-8';
    protected $serviceUrl = '';
    protected $apiKey = '';
    protected $user = '';
    protected $password = '';

    /** @var \CentralNews\Model\SubscriberManager */
    protected $subscriberManager;

    /** @var \CentralNews\Model\OrderManager */
    protected $orderManager;

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
        return new \CentralNews\Model\SubscriberManager($this);
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
        return new \CentralNews\Model\OrderManager($this);
    }

    /**
     * @throws \CentralNews\Exception\Exception
     * @return \nusoap_client
     */
    public function createApiClient()
    {
        $nuSoap = new \nusoap_client($this->getServiceUrl() . '?wsdl', 'wsdl', false, false, false, false, 0, 10);
        $nuSoap->soap_defencoding = $this->getEncoding();

        if ($nuSoap->getError()) {
            throw new Exception\Exception(gettext('nepodařilo se inicializovat SOAP klienta'));
        }

        return $nuSoap;
    }

    /**
     * @return array
     */
    public function getSoapHeaders()
    {
        $headers = array(
            'api_key' => $this->getApiKey(),
            'api_name' => $this->getUser(),
            'api_pass' => $this->getPassword(),
        );

        return $headers;
    }

    /**
     * @return \CentralNews\Entity\SubscriberGroup[]
     */
    public function getSubscribersGroups()
    {
        $manager = $this->getSubscriberManager();
        return $manager->getGroups();
    }

    /**
     * @return string
     */
    public function getServiceUrl()
    {
        return $this->serviceUrl;
    }

    /**
     * @param string $serviceUrl
     * @return \CentralNews\Service\Client
     */
    public function setServiceUrl($serviceUrl)
    {
        $this->serviceUrl = $serviceUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     * @return this
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     * @return this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param string $encoding
     * @return this
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
        return $this;
    }

}