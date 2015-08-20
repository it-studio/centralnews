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
     * ziska z CN seznam skupin, do kterych je mozne odberatele zaradit
     * @throws \Exception
     * @return array
     */
    public function getSubscribersGroups()
    {
        $groups = array();

        $rawResponse = $this->createApiClient()->call('get_subscriber_groups', array(), '', '', $this->getSoapHeaders());
        $response = new Response($rawResponse);

        $xml = $response->getResult();
        if ($xml instanceof \SimpleXMLElement) {

            foreach ($xml->groups[0]->group as $group) {
                $attr = $group->attributes();
                $groups[(int) $attr->id] = (string) $attr->name;
            }

            return $groups;
        } else {
            throw new \Exception(gettext("chyba při parsování seznamu skupin"));
        }
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