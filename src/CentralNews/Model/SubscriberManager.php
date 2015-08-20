<?php

namespace CentralNews\Model;

use CentralNews\Service\Client;
use CentralNews\Service\Response;
use CentralNews\Entity\Subscriber;

class SubscriberManager extends Manager
{
    protected $centralNewsApi = null;

    public function __construct(Client $centralNewsApi)
    {
        $this->centralNewsApi = $centralNewsApi;
    }

    // Nacte odberatele z dane skupiny na zaklade jeho emailu
    public function getSubscriberByEmail($email)
    {
        $param = array(
            'group_id' => $this->getIdGroup(),
            'subscriber_email' => $email,
        );

        $rawResponse = $this->centralNewsApi->createApiClient()->call('get_subscriber', $param, '', '', $this->centralNewsApi->getSoapHeaders());
        $response = new Response($rawResponse);
        if($response->isError()) {
            throw new \Exception($response->getMessage());
        }

        // vraceny subscriber. pokud neni v odpovedi xml s daty subscribera (pokud nebyl v CN nalezen), vracime null
        $subscriber = null;
        $xmlSubscriber = $response->getResult();

        if($xmlSubscriber instanceof \SimpleXMLElement) {
            $subscriber = Subscriber::createFromXml($xmlSubscriber);
        }

        return $subscriber;
    }

    // Aktualizuje udaje o uzivateli v CN. Pokud neexistuje, vytvori se.
    public function updateSubscriber(Subscriber $subscriber)
    {
        $xmlData = $this->getXml($subscriber);
        $encodedXmlData = base64_encode($xmlData);

        $param = array(
            'group_id' => $this->getIdGroup(),
            'subscribers' => $encodedXmlData
        );

        $rawResponse = $this->centralNewsApi->createApiClient()->call('import_subscribers', $param, '', '', $this->centralNewsApi->getSoapHeaders());
        return new Response($rawResponse);
    }

    // smaze odberatele z CentralNews
    public function deleteSubscriber($subscriber)
    {
        $email = $subscriber instanceof Subscriber ? $subscriber->getEmail() : $subscriber;
        $param = array(
            'group_id' => $this->getIdGroup(),
            'subscriber_email' => $email,
        );

        $rawResponse = $this->centralNewsApi->createApiClient()->call('delete_subscriber', $param, '', '', $this->centralNewsApi->getSoapHeaders());
        return new Response($rawResponse);
    }

    protected function getXml(Subscriber $subscriber)
    {
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement("subscribers");
        $xml->writeAttribute('enable_update', true);
        $xml->writeAttribute('group_id', $this->getIdGroup());

        $xml->startElement("subscriber");
        $xml->writeAttribute('email', $subscriber->getEmail());
        $xml->writeAttribute('firstname', $subscriber->getFirstname());
        $xml->writeAttribute('surname', $subscriber->getSurname());
        $xml->writeAttribute('city', $subscriber->getCity());
        $xml->writeAttribute('address', $subscriber->getAddress());
        $xml->writeAttribute('zip_code', $subscriber->getZipCode());
        $xml->writeAttribute('company', $subscriber->getCompany());
        $xml->writeAttribute('status_activity', $subscriber->getStatusActivityString());
        $xml->writeAttribute('status_activity_rewrite', $subscriber->getStatusActivityRewrite());
        $xml->endElement();

        $xml->endElement();

        return $xml->flush();
    }

}