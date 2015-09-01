<?php

namespace CentralNews\Entity;

use CentralNews\Exception\InvalidArgumentException;
use CentralNews\Exception\DomainException;
use CentralNews\Service\Client;

class Subscriber
{
    const ACTIVE = 'aktivni';
    const LOGOUT = 'odhlaseny';
    const INVALID_EMAIL = 'chybny_email';

    /** @var string */
    protected $email;

    /** @var string */
    protected $phone;
    protected $firstname = null;
    protected $surname = null;
    protected $city = null;
    protected $address = null;
    protected $zipCode = null;
    protected $company = null;
    protected $phoneNumber = null;
    protected $statusActivity = null;
    protected $statusActivityRewrite = null;
    protected $statusConfirmation;

    /** @var string */
    protected $status;

    /**
     * @param string $data
     * @throws InvalidArgumentException
     */
    public function __construct($data = null)
    {
        if ($data) {
            if (Client::isEmail($data)) {
                $this->setEmail($data);
            } else {
                throw new InvalidArgumentException('Invalid email');
            }
        }
    }

    public static function createFromXml(SimpleXMLElement $simpleXmlData)
    {
        $subscriberData = array();

        foreach ($simpleXmlData->subscriber[0]->attributes() as $key => $value) {
            $subscriberData[$key] = (string) $value;
        }

        return self::fromArray($subscriberData);
    }

    public static function fromArray($subscriberData)
    {
        $subscriber = new self($subscriberData['email']);

        if (isset($subscriberData['firstname'])) {
            $subscriber->setFirstname($subscriberData['firstname']);
        }

        if (isset($subscriberData['surname'])) {
            $subscriber->setSurname($subscriberData['surname']);
        }

        if (isset($subscriberData['address'])) {
            $subscriber->setAddress($subscriberData['address']);
        }

        if (isset($subscriberData['city'])) {
            $subscriber->setCity($subscriberData['city']);
        }

        if (isset($subscriberData['zip_code'])) {
            $subscriber->setZipCode($subscriberData['zip_code']);
        }

        if (isset($subscriberData['company'])) {
            $subscriber->setCompany($subscriberData['company']);
        }

        if (isset($subscriberData['phone_number'])) {
            $subscriber->setPhoneNumber($subscriberData['phone_number']);
        }

        if (isset($subscriberData['status_activity'])) {
            $subscriber->setStatusActivity($subscriberData['status_activity']);
        }

        if (isset($subscriberData['status_confirmation'])) {
            $subscriber->setStatusConfirmation($subscriberData['status_confirmation']);
        }

        return $subscriber;
    }

    public function getEmail($throw = true)
    {
        if (!$this->email && $throw) {
            throw new DomainException('Email is not set');
        }
        return $this->email;
    }

    public function setEmail($email)
    {
        if (!Client::isEmail($email)) {
            throw new InvalidArgumentException('Invalid email');
        }
        $this->email = $email;
        return $this;
    }

    public function getPhone($throw = true)
    {
        if (!$this->phone && $throw) {
            throw new DomainException('Phone is not set');
        }
        return $this->phone;
    }

    public function setPhone($phone)
    {
        if (!$phone) {
            throw new InvalidArgumentException('Invalid phone');
        }
        $this->phone = $phone;
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

    public function getStatus()
    {
        return $this->status ? $this->status : self::LOGOUT;
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

    public function getStatusConfirmation()
    {
        return $this->statusConfirmation;
    }

    public function setStatusConfirmation($statusConfirmation)
    {
        $this->statusConfirmation = $statusConfirmation;
    }

    public function setStatus($status)
    {
        if ($status == self::ACTIVE || $status == self::LOGOUT || $status == self::INVALID_EMAIL) {
            $this->status = $status;
        } else {
            throw new InvalidArgumentException('Invalid status');
        }
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        if (!$this->getEmail(false) && !$this->getPhone(false)) {
            return false;
        }
        return true;
    }

}