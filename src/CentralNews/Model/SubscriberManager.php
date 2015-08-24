<?php

namespace CentralNews\Model;

use CentralNews\Service\Response;
use CentralNews\Entity\Subscriber;
use CentralNews\Entity\SubscriberGroup;
use CentralNews\Exception;

class SubscriberManager extends Manager
{
    /** @var \CentralNews\Entity\SubscriberGroup[] */
    protected $groups;

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
     * @param \CentralNews\Entity\SubscriberGroup $group
     * @return \CentralNews\Service\Response
     */
    public function deleteSubscriber($subscriber, SubscriberGroup $group)
    {
        $email = $subscriber instanceof Subscriber ? $subscriber->getEmail() : $subscriber;
        $param = array(
            'group_id' => $group->getId(),
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
     * @return \CentralNews\Entity\SubscriberGroup[]
     */
    public function loadGroups()
    {
        $groups = array();

        $request = new \CentralNews\Service\Request('get_subscriber_groups', array(), '', '', $this->centralNewsApi->getSoapHeaders());
        $response = $this->sendRequest($request);

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

    /**
     * @return \CentralNews\Entity\SubscriberGroup[]
     */
    public function getGroups()
    {
        if (!$this->groups) {
            $this->groups = $this->loadGroups();
        }
        return $this->groups;
    }

    /**
     * @param \CentralNews\Entity\SubscriberGroup|null $group
     * @return int
     */
    public function getSubscribersCount(SubscriberGroup $group = null)
    {
        $data = array();
        if ($group) {
            $data['group_id'] = $group->getId();
        }

        $request = new \CentralNews\Service\Request('get_subscribers_count', $data, '', '', $this->centralNewsApi->getSoapHeaders());
        $response = $this->sendRequest($request);

        return (int) $response->getResult()->count->attributes()->count;
    }

    /**
     * description neni definovane v xml pdf 
     * @param type $group
     * @return SubscriberGroup
     */
    public function addGroup(SubscriberGroup $group)
    {
        $xmlData = $this->createXmlSubscriberGroup($group);
        $encodedXmlData = base64_encode($xmlData);

        $data = array(
            'groups' => $encodedXmlData
        );

        $request = new \CentralNews\Service\Request('add_subscriber_groups', $data, '', '', $this->centralNewsApi->getSoapHeaders());
        $response = $this->sendRequest($request);

        // pocet vyslednych skupin> $xml->groups->attributes()->count
        $out = array();
        foreach ($response->getResult()->groups->group[0]->attributes() as $attrName => $attrVal) {
            $out[$attrName] = (string) $attrVal;
        }
        $newGroup = new SubscriberGroup($out);
        $this->groups = $this->getGroups() + array($newGroup->getId() => $newGroup);
        return $newGroup;
    }

    /**
     * @param SubscriberGroup $subscriber
     * @return string
     */
    protected function createXmlSubscriberGroup(SubscriberGroup $subscriber)
    {
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement("groups");
        $xml->startElement("group");
        $xml->writeAttribute('name', $subscriber->getName());
        $xml->writeAttribute('firstname', $subscriber->getDescription());
        $xml->endElement();
        $xml->endElement();

        return $xml->flush();
    }

}