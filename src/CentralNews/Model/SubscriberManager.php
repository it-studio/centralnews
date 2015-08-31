<?php

namespace CentralNews\Model;

use CentralNews\Service\Request;
use CentralNews\Entity\Subscriber;
use CentralNews\Entity\SubscriberGroup;
use CentralNews\Entity\ISubscriberGroup;
use CentralNews\Entity\BaseSubscriberGroup;
use CentralNews\Exception\Exception;
use CentralNews\Exception\InvalidArgumentException;

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
        return $response->isSuccess();
    }

    /**
     * Aktualizuje udaje o uzivateli v CN. Pokud neexistuje, vytvori se.
     * @param \CentralNews\Entity\Subscriber $subscriber
     * @param \CentralNews\Entity\ISubscriberGroup $group
     * @return bool
     */
    public function saveSubscriber(Subscriber $subscriber, ISubscriberGroup $group)
    {
        return $this->saveSubscribers(array($subscriber), $group);
    }

    /**
     * @param \CentralNews\Entity\Subscriber[] $subscribers
     * @param \CentralNews\Entity\ISubscriberGroup $group
     * @param array $options
     * @return bool
     */
    public function saveSubscribers(array $subscribers, ISubscriberGroup $group, array $options = array())
    {
        $xml = $this->createXmlSubscribers($subscribers, $group, $options);
        $param = array(
            'group_id' => (int) $group->getId(),
            'subscribers' => base64_encode($xml)
        );

        $request = new Request('import_subscribers', $param, '', '');
        $response = $this->sendRequest($request);

        return $response->isSuccess();
    }

    /**
     * @param \CentralNews\Entity\Subscriber[] $subscribers
     * @param \CentralNews\Entity\ISubscriberGroup $group
     * @return bool
     */
    public function importSubscribers(array $subscribers, ISubscriberGroup $group)
    {
        return $this->saveSubscribers($subscribers, $group, array('enable_update' => false));
    }

    /**
     * @param \CentralNews\Entity\Subscriber $subscriber
     * @param \CentralNews\Entity\ISubscriberGroup $group
     * @return bool
     */
    public function importSubscriber(Subscriber $subscriber, ISubscriberGroup $group)
    {
        return $this->importSubscribers(array($subscriber), $group);
    }

    /**
     * @return \CentralNews\Entity\BaseSubscriberGroup[]|false
     */
    public function loadGroups()
    {
        $request = new Request('get_subscriber_groups', array(), '', '');
        $response = $this->sendRequest($request);

        if (!$response->isSuccess()) {
            return false;
        }

        $groups = array();
        foreach ($response->getResult()->groups[0]->group as $group) {
            $attr = $group->attributes();
            $id = (int) $attr->id;
            $name = (string) $attr->name;
            $groups[$id] = new BaseSubscriberGroup(array('id' => $id, 'name' => $name));
        }

        return $groups;
    }

    /**
     * @return \CentralNews\Entity\BaseSubscriberGroup[]|false
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
     * @return int|false
     */
    public function getSubscribersCount(ISubscriberGroup $group = null)
    {
        $data = array();
        if ($group) {
            if (!$group->getId()) {
                throw new InvalidArgumentException('Invalid Group ID');
            }
            $data['group_id'] = $group->getId();
        }

        $request = new Request('get_subscribers_count', $data, '', '');
        $response = $this->sendRequest($request);

        return $response->isSuccess() ? (int) $response->getResult()->count->attributes()->count : false;
    }

    /**
     * @param \CentralNews\Entity\ISubscriberGroup|null $group
     * @throws \CentralNews\Exception\InvalidArgumentException
     * @return array|false
     */
    public function getSubscriberFields(ISubscriberGroup $group = null)
    {
        $data = array();
        if ($group) {
            if (!$group->getId()) {
                throw new InvalidArgumentException('Invalid Group ID');
            }
            $data['group_id'] = $group->getId();
        }

        $request = new Request('get_subscriber_fields', $data, '', '');
        $response = $this->sendRequest($request);

        if (!$response->isSuccess()) {
            return false;
        }

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
                throw new InvalidArgumentException('Invalid Group ID');
            }
            $data['group_id'] = $group->getId();
        }
        $data['subscriber_email'] = $email;

        $request = new \CentralNews\Service\Request('get_subscriber', $data, '', '');
        $response = $this->sendRequest($request);

        if ($response->isSuccess()) {
            return false;
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
     * @return BaseSubscriberGroup|false
     */
    public function addGroup(SubscriberGroup $group)
    {
        $xmlData = $this->createXmlSubscriberGroup($group);

        $data = array(
            'groups' => base64_encode($xmlData)
        );

        $request = new \CentralNews\Service\Request('add_subscriber_groups', $data, '', '');
        $response = $this->sendRequest($request);

        if (!$response->isSuccess()) {
            return false;
        }

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
        $xml->writeAttribute('description', $subscriber->getDescription());
        $xml->endElement();
        $xml->endElement();

        return $xml->flush();
    }

}