<?php

namespace CentralNews\Service;

class Client
{
	protected $encoding = 'UTF-8';
	protected $serviceUrl = '';
	protected $apiKey = '';
	protected $user = '';
	protected $password = '';

	public function createApiClient()
	{
		$nuSoap = new \nusoap_client($this->getServiceUrl() . '?wsdl', 'wsdl', false, false, false, false, 0, 10);
		$nuSoap->soap_defencoding = $this->getEncoding();

		if($nuSoap->getError()) {
			throw new \Exception(gettext('nepodařilo se inicializovat SOAP klienta'));
		}

		return $nuSoap;
	}
	
	public function getSoapHeaders()
	{
		$headers = array(
			'api_key'	=> $this->getApiKey(),
			'api_name'	=> $this->getUser(),
			'api_pass'	=> $this->getPassword(),
		);

		return $headers;
	}
	
	// ziska z CN seznam skupin, do kterych je mozne odberatele zaradit
	public function getSubscribersGroups()
	{
		$groups = array();
		
		$rawResponse = $this->createApiClient()->call('get_subscriber_groups', array(), '', '', $this->getSoapHeaders());
		$response = new Response($rawResponse);

		$xml = $response->getResult();
		if($xml instanceof \SimpleXMLElement) {
			
			foreach($xml->groups[0]->group as $group) {
				$attr = $group->attributes();
				$groups[(int) $attr->id] = (string) $attr->name;
			}

			return $groups;
			
		} else {
			throw new \Exception(gettext("chyba při parsování seznamu skupin"));
		}
	}

	public function getServiceUrl()
	{
		return $this->serviceUrl;
	}

	public function setServiceUrl($serviceUrl)
	{
		$this->serviceUrl = $serviceUrl;
		return $this;
	}

	public function getApiKey()
	{
		return $this->apiKey;
	}

	public function setApiKey($apiKey)
	{
		$this->apiKey = $apiKey;
		return $this;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}
	
	public function getEncoding()
	{
		return $this->encoding;
	}

	public function setEncoding($encoding)
	{
		$this->encoding = $encoding;
		return $this;
	}

}