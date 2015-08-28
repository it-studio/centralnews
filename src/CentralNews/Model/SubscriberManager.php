<?php

namespace CentralNews\Model;

use CentralNews\Service\Request;
use CentralNews\Entity\Subscriber;
use CentralNews\Entity\SubscriberGroup;
use CentralNews\Entity\ISubscriberGroup;
use CentralNews\Entity\BaseSubscriberGroup;
use CentralNews\Exception;

class SubscriberManager extends Manager
{
    /** @var \CentralNews\Entity\BaseSubscriberGroup[] */
    protected $groups;

    /**
     * @param \CentralNews\Service\Subscriber|email $subscriber
     * @param \CentralNews\Entity\ISubscriberGroup $group
     * @return bool
     */
    public function deleteSubscriber($subscriber, ISubscriberGroup $group)
    {
        $email = $subscriber instanceof Subscriber ? $subscriber->getEmail() : $subscriber;
        $param = array(
            'group_id' => $group->getId(),
            'subscriber_email' => $email,
        );

        $request = new Request('delete_subscriber', $param, '', '');
        $response = $this->sendRequest($request);
        return $response->getStatus() == 'success';
    }

    /**
     * Aktualizuje udaje o uzivateli v CN. Pokud neexistuje, vytvori se.
     * @param Subscriber $subscriber
     * @return \CentralNews\Service\Response
     */
    public function saveSubscriber(Subscriber $subscriber, ISubscriberGroup $group)
    {
        return $this->saveSubscribers(array($subscriber), $group);
    }

    /**
     * 
     * @param array $subscribers
     * @param ISubscriberGroup $group
     * @param array $options
     * @return type
     */
    public function saveSubscribers(array $subscribers, ISubscriberGroup $group, array $options = array())
    {
        $xml = $this->createXmlSubscribers($subscribers, $group, $options);
        $param = array(
            'group_id' => $group->getId(),
            'subscribers' => base64_encode($xml)
        );

        $request = new Request('import_subscribers', $param, '', '');
        $response = $this->sendRequest($request);

        return $response->getStatus() == 'success';
    }

    public function importSubscribers(array $subscribers, ISubscriberGroup $group)
    {
        return $this->saveSubscribers($subscribers, $group, array('enable_update' => false));
    }

    public function importSubscriber(Subscriber $subscriber, ISubscriberGroup $group)
    {
        return $this->importSubscribers(array($subscriber), $group);
    }

    /**
     * @throws \Exception
     * @return \CentralNews\Entity\BaseSubscriberGroup[]
     */
    public function loadGroups()
    {
        $groups = array();

        $request = new \CentralNews\Service\Request('get_subscriber_groups', array(), '', '');
        $response = $this->sendRequest($request);

        $xml = $response->getResult();
        if ($xml instanceof \SimpleXMLElement) {

            foreach ($xml->groups[0]->group as $group) {
                $attr = $group->attributes();
                $id = (int) $attr->id;
                $name = (string) $attr->name;
                $groups[$id] = new BaseSubscriberGroup(array('id' => $id, 'name' => $name));
            }

            return $groups;
        } else {
            throw new \Exception(gettext("chyba při parsování seznamu skupin"));
        }
    }

    /**
     * @return \CentralNews\Entity\BaseSubscriberGroup[]
     */
    public function getGroups()
    {
        if ($this->groups === null) {
            $this->groups = $this->loadGroups();
        }
        return $this->groups;
    }

    /**
     * @param \CentralNews\Entity\ISubscriberGroup|null $group
     * @throws \CentralNews\Exception\InvalidArgumentException
     * @return int
     */
    public function getSubscribersCount(ISubscriberGroup $group = null)
    {
        $data = array();
        if ($group) {
            if (!$group->getId()) {
                throw new Exception\InvalidArgumentException;
            }
            $data['group_id'] = $group->getId();
        }

        $request = new \CentralNews\Service\Request('get_subscribers_count', $data, '', '');
        $response = $this->sendRequest($request);

        return (int) $response->getResult()->count->attributes()->count;
    }

    /**
     * @param \CentralNews\Entity\ISubscriberGroup|null $group
     * @throws \CentralNews\Exception\InvalidArgumentException
     * @return array
     */
    public function getSubscriberFields(ISubscriberGroup $group = null)
    {
        $data = array();
        if ($group) {
            if (!$group->getId()) {
                throw new Exception\InvalidArgumentException;
            }
            $data['group_id'] = $group->getId();
        }

        $request = new \CentralNews\Service\Request('get_subscriber_fields', $data, '', '');
        $response = $this->sendRequest($request);
        $out = array();
        foreach ($response->getResult()->subscriberField->attributes() as $attrName => $attrVal) {
            $out[$attrName] = (string) $attrVal;
        }

        return $out;
    }

    public function getSubscriber($subscriber, ISubscriberGroup $group)
    {
        $email = $subscriber instanceof Subscriber ? $subscriber->getEmail() : $subscriber;
        $data = array();
        if ($group) {
            if (!$group->getId()) {
                throw new Exception\InvalidArgumentException;
            }
            $data['group_id'] = $group->getId();
        }
        $data['subscriber_email'] = $email;

        $request = new \CentralNews\Service\Request('get_subscriber', $data, '', '');
        $response = $this->sendRequest($request);

        if ($response->getStatus() == 'error') {
            return FALSE;
        }

        // pocet vyslednych skupin: $xml->groups->attributes()->count
        $out = array();
        foreach ($response->getResult()->subscriber->attributes() as $attrName => $attrVal) {
            $out[$attrName] = (string) $attrVal;
        }
        /** @todo getSubscriberFields + subscriberObject */
        $map = array(
            'email' => $out['SUBSCRIBER_EMAIL'],
            'firstname' => $out['SUBSCRIBER_FIRSTNAME'],
            'status_activity' => $out['status_activity'],
            'status_confirmation' => $out['status_confirmation'],
            'surname' => $out['SUBSCRIBER_SURNAME'],
            'city' => $out['SUBSCRIBER_CITY'],
            'gender' => $out['SUBSCRIBER_GENDER'],
            'main_order' => $out['SUBSCRIBER_MAIN_ORDER'],
        );

        return !empty($out) ? Subscriber::fromArray($map) : false;
    }

    /**
     * @param \CentralNews\Entity\SubscriberGroup $group
     * @return BaseSubscriberGroup
     */
    public function addGroup(SubscriberGroup $group)
    {
        $xmlData = $this->createXmlSubscriberGroup($group);
        $encodedXmlData = base64_encode($xmlData);

        $data = array(
            'groups' => $encodedXmlData
        );

        $request = new \CentralNews\Service\Request('add_subscriber_groups', $data, '', '');
        $response = $this->sendRequest($request);

        // pocet vyslednych skupin> $xml->groups->attributes()->count
        $out = array();
        foreach ($response->getResult()->groups->group[0]->attributes() as $attrName => $attrVal) {
            $out[$attrName] = (string) $attrVal;
        }
        $newGroup = new BaseSubscriberGroup($out);
        $this->groups = $this->getGroups() + array($newGroup->getId() => $newGroup);
        return $newGroup;
    }

    /**
     * @param \CentralNews\Service\Subscriber $subscriber
     * @return string|false
     */
    protected function createXmlSubscribers(array $subscribers, ISubscriberGroup $group, array $options)
    {
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement("subscribers");
        $xml->writeAttribute('enable_update', isset($options['enable_update']) ? (bool) $options['enable_update'] : TRUE);
        $xml->writeAttribute('group_id', (int) $group->getId());

        if ($group instanceof BaseSubscriberGroup && !$group->getId() && $group->getName()) {
            $xml->writeAttribute('subscriber_group_name', $group->getName());
        }

        foreach ($subscribers as $subscriber) {
            $xml->startElement("subscriber");
            $xml->writeAttribute('email', $subscriber->getEmail());
            $xml->writeAttribute('firstname', $subscriber->getFirstname());
            $xml->writeAttribute('surname', $subscriber->getSurname());
            $xml->writeAttribute('city', $subscriber->getCity());
            $xml->writeAttribute('address', $subscriber->getAddress());
            $xml->writeAttribute('zip_code', $subscriber->getZipCode());
            $xml->writeAttribute('company', $subscriber->getCompany());
            $xml->writeAttribute('status_activity', $subscriber->getStatus());
            $xml->writeAttribute('status_activity_rewrite', $subscriber->getStatusActivityRewrite());
            $xml->endElement();
        }
        $xml->endElement();

        return $xml->flush();
    }

}