<?php

namespace CentralNews\Entity;

class Subscriber
{
	const ACTIVE = 'aktivni';
	const LOGOUT = 'odhlaseny';

	protected $email = "";
	protected $firstname = null;
	protected $surname = null;
	protected $city = null;
	protected $address = null;
	protected $zipCode = null;
	protected $company = null;
	protected $phoneNumber = null;
	protected $statusActivity = null;
	protected $statusActivityRewrite = null;

	public function __construct($email)
	{
		if(empty($email)) {
			throw new Exception(gettext("neznámý email odběratele"));
		}
		$this->setEmail($email);
	}

	public static function createFromXml(SimpleXMLElement $simpleXmlData)
	{
		$subscriberData = array();

		foreach($simpleXmlData->subscriber[0]->attributes() as $key => $value) {
			$subscriberData[$key] = (string) $value;
		}

		return self::fromArray($subscriberData);
	}

	public static function fromArray($subscriberData)
	{
		$subscriber = new self($subscriberData['email']);

		if(isset($subscriberData['firstname'])) {
			$subscriber->setFirstname($subscriberData['firstname']);
		}

		if(isset($subscriberData['surname'])) {
			$subscriber->setSurname($subscriberData['surname']);
		}

		if(isset($subscriberData['address'])) {
			$subscriber->setAddress($subscriberData['address']);
		}

		if(isset($subscriberData['city'])) {
			$subscriber->setCity($subscriberData['city']);
		}

		if(isset($subscriberData['zip_code'])) {
			$subscriber->setZipCode($subscriberData['zip_code']);
		}

		if(isset($subscriberData['company'])) {
			$subscriber->setCompany($subscriberData['company']);
		}

		if(isset($subscriberData['phone_number'])) {
			$subscriber->setPhoneNumber($subscriberData['phone_number']);
		}

		return $subscriber;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	public function getFirstname()
	{
		return $this->firstname;
	}

	public function setFirstname($firstname)
	{
		$this->firstname = $firstname;
		return $this;
	}

	public function getSurname()
	{
		return $this->surname;
	}

	public function setSurname($surname)
	{
		$this->surname = $surname;
		return $this;
	}

	public function getCity()
	{
		return $this->city;
	}

	public function setCity($city)
	{
		$this->city = $city;
		return $this;
	}

	public function getAddress()
	{
		return $this->address;
	}

	public function setAddress($address)
	{
		$this->address = $address;
		return $this;
	}

	public function getZipCode()
	{
		return $this->zipCode;
	}

	public function setZipCode($zipCode)
	{
		$zipCode = preg_replace("/[^0-9]+/", "", $zipCode);
		$this->zipCode = $zipCode;
		return $this;
	}

	public function getCompany()
	{
		return $this->company;
	}

	public function setCompany($company)
	{
		$this->company = $company;
		return $this;
	}

	public function getPhoneNumber()
	{
		return $this->phoneNumber;
	}

	public function setPhoneNumber($phoneNumber)
	{
		$this->phoneNumber = $phoneNumber;
		return $this;
	}
	
	public function getStatusActivityString()
	{
		if (!is_null($this->getStatusActivity())) {
			return $this->getStatusActivity() ? self::ACTIVE : self::LOGOUT;
		}
		return  null;
	}

	public function getStatusActivity()
	{
		return $this->statusActivity;
	}

	public function setStatusActivity($statusActivity)
	{
		$this->statusActivity = (bool) $statusActivity;
		return $this;
	}
	
	public function getStatusActivityRewrite()
	{
		return $this->statusActivityRewrite;
	}

	public function setStatusActivityRewrite($statusActivityRewrite)
	{
		$this->statusActivityRewrite = (int) $statusActivityRewrite;
		return $this;
	}

}