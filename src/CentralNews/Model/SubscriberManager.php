<?php

namespace CentralNews\Model;

use CentralNews\Service\Response;
use CentralNews\Entity\Subscriber;
use CentralNews\Entity\SubscriberGroup;
use CentralNews\Exception;

class SubscriberManager extends Manager
{

    /**
     * Nacte odberatele z dane skupiny na zaklade jeho emailu
     * @param type $email
     * @throws \Exception
     * @return \CentralNews\Entity\Subscriber
     */
    public function getSubscriberByEmail($email)
    {
        $param = array(
            'group_id' => $this->getIdGroup(),
            'subscriber_email' => $email,
        );

        $rawResponse = $this->centralNewsApi->createApiClient()->call('get_subscriber', $param, '', '', $this->centralNewsApi->getSoapHeaders());
        $response = new Response($rawResponse);
        if ($response->isError()) {
            throw new Exception\Exception($response->getMessage());
        }

        // vraceny subscriber. pokud neni v odpovedi xml s daty subscribera (pokud nebyl v CN nalezen), vracime null
        $subscriber = null;
        $xmlSubscriber = $response->getResult();

        if ($xmlSubscriber instanceof \SimpleXMLElement) {
            $subscriber = Subscriber::createFromXml($xmlSubscriber);
        }

        return $subscriber;
    }

    /**
     * Aktualizuje udaje o uzivateli v CN. Pokud neexistuje, vytvori se.
     * @param Subscriber $subscriber
     * @return \CentralNews\Service\Response
     */
    public function saveSubscriber(Subscriber $subscriber)
    {
        $xmlData = $this->createXml($subscriber);
        $encodedXmlData = base64_encode($xmlData);

        $param = array(
            'group_id' => $this->getIdGroup(),
            'subscribers' => $encodedXmlData
        );

        $rawResponse = $this->centralNewsApi->createApiClient()->call('import_subscribers', $param, '', '', $this->centralNewsApi->getSoapHeaders());
        return new Response($rawResponse);
    }

    /**
     * @param \CentralNews\Service\Subscriber|email $subscriber
     * @return \CentralNews\Service\Response
     */
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

    /**
     * @param \CentralNews\Service\Subscriber $subscriber
     * @return string|false
     */
    protected function createXml(Subscriber $subscriber)
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

    /**
     * @throws \Exception
     * @return \CentralNews\Entity\SubscriberGroup
     */
    public function getGroups()
    {
        $groups = array();

        $response = $this->sendRequest('get_subscriber_groups', array(), '', '', $this->centralNewsApi->getSoapHeaders());

        $xml = $response->getResult();
        if ($xml instanceof \SimpleXMLElement) {

            foreach ($xml->groups[0]->group as $group) {
                $attr = $group->attributes();
                $id = (int) $attr->id;
                $name = (string) $attr->name;
                $groups[$id] = new SubscriberGroup(array('id' => $id, 'name' => $name));
            }

            return $groups;
        } else {
            throw new \Exception(gettext("chyba při parsování seznamu skupin"));
        }
    }

    public function saveGroup(SubscriberGroup $group)
    {

    }

}