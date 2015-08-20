<?php

namespace CentralNews\Service;

use CentralNews\Entity\Subscriber;

class Response
{
    protected $result = null; // instance SimpleXMLElement reprezentujici vracena data (napr. data nalezeneho subscribera)
    protected $errors = array(); // instance SimpleXMLElement reprezentujici vracene chyby
    protected $errorsForSubscribers = array(); // pole chyb pro jednotlive odberatele (klic je email odberatele, hodnota je pole chyb v lokalnim kodovani)
    protected $errorsForOrders = array(); // pole chyb pro jednotlive objednavky (klic je poradove cislo objednavky, hodnota je pole chyb v lokalnim kodovani)
    protected $status = self::STATUS_SUCCESS; // status odpovedi (erros/success)
    protected $message = ""; // textove shrnuji odpovedi

    const STATUS_SUCCESS = "success";
    const STATUS_ERROR = "error";

    public function __construct($rawResponse)
    {
        if(is_array($rawResponse)) {

            $this->setStatus(self::STATUS_ERROR);
            $this->setMessage($rawResponse['faultstring']);

            throw new \Exception($rawResponse['faultstring']);
        } elseif(!empty($rawResponse)) {

            try {
                $xml = new \SimpleXMLElement(base64_decode($rawResponse));

                $this->setStatus((string) $xml->status[0]['type']);
                $this->setMessage((string) $xml->status[0]['msg']);
                $this->setResult($xml->result[0]);
                $this->setErrors($xml->errors[0]);

                $this->parseErrors($xml->errors[0]);
            } catch(\Exception $e) {
                throw new \Exception("chyba při parsování odpovědi");
            }
        } else {
            throw new \Exception("z CentralNews nedorazila odpověď");
        }
    }

    protected function parseErrors($xml)
    {
        foreach($xml->subscriber as $subscrNode) {

            $attrs = $subscrNode->attributes();
            $email = $attrs['email'];

            $errFields = array();

            foreach($subscrNode->error as $errNode) {

                $errAttrs = $errNode->attributes();

                if(!empty($errAttrs['errorField'])) {
                    $errFields[] = (string) $errAttrs['errorField'];
                } else {
                    $this->addErrorForSubscriber((string) $errAttrs['msg'], (string) $email);
                }
            }
            if(!empty($errFields)) {
                $this->addErrorForSubscriber(sprintf("chybně vyplněné pole %s", implode(", ", $errFields)), (string) $email);
            }
        }

        foreach($xml->order as $orderNode) {

            $attrs = $orderNode->attributes();

            foreach($orderNode->error as $errNode) {
                $errAttrs = $errNode->attributes();
                $this->addErrorForOrders((string) $errAttrs['msg'], (string) $attrs['count']);
            }
        }
    }

    public function getErrorsForSubscriber($subscriber)
    {
        $subscriberEmail = $subscriber instanceof Subscriber ? $subscriber->getEmail() : $subscriber;
        return $this->errorsForSubscribers[$subscriberEmail];
    }

    public function addErrorForSubscriber($error, $subscriberEmail)
    {
        $this->errorsForSubscribers[$subscriberEmail][] = $error;
        return $this;
    }

    public function getErrorsForOrders($count)
    {
        return $this->errorsForOrders[$count];
    }

    public function addErrorForOrders($error, $count)
    {
        $this->errorsForOrders[$count][] = $error;
        return $this;
    }

    public function isError()
    {
        return $this->getStatus() === self::STATUS_ERROR;
    }

    public function isSuccess()
    {
        return $this->getStatus() === self::STATUS_SUCCESS;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

}