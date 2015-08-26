<?php

namespace CentralNews\Model;

use CentralNews\Entity\Order;
use CentralNews\Entity\SubscriberGroup;
use CentralNews\Exception\InvalidArgumentException;

class OrderManager extends Manager
{
    /** @var callable[]  function (\CentralNews\Model\OrderManager $manager, \CentralNews\Service\Response $response); */
    public $onImportedOrders;

    /**
     * @param \CentralNews\Entity\Order[] $orders
     * @param \CentralNews\Entity\SubscriberGroup $group
     * @return bool
     * @throws \CentralNews\Exception\InvalidArgumentException
     */
    public function importOrders(array $orders, SubscriberGroup $group)
    {
        if (!$group->getId()) {
            throw new InvalidArgumentException;
        }
        $xmlOrders = $this->createOrdersXml($orders);

        $data = array(
            'group_id' => $group->getId(),
            'orders' => base64_encode($xmlOrders),
        );

        $request = new \CentralNews\Service\Request('import_orders', $data, '', '');
        $response = $this->sendRequest($request);
        $this->onImportedOrders($this, $response);
        return $response->getStatus() == 'success';
    }

    /**
     * @param \CentralNews\Entity\Order $order
     * @param \CentralNews\Entity\SubscriberGroup $group
     * @return bool
     * @throws \CentralNews\Exception\InvalidArgumentException
     */
    public function importOrder(Order $order, SubscriberGroup $group)
    {
        return $this->importOrders(array($order), $group);
    }

    /**
     * @param Order[] $orders
     * @return string|false
     */
    protected function createOrdersXml(array $orders)
    {
        $xml = new \DOMDocument();
        $xml->formatOutput = true;
        $root = $xml->appendChild($xml->createElement("orders"));

        foreach ($orders as $order) {
            $element = $root->appendChild($xml->createElement("order"));

            $orderNumber = $xml->createElement("order_number");
            $orderNumber->appendChild($xml->createCDATASection($order->getOrderNumber()));
            $element->appendChild($orderNumber);

            $orderPrice = $xml->createElement("total_price");
            $orderPrice->appendChild($xml->createCDATASection($order->getOrderTotalPrice()));
            $element->appendChild($orderPrice);

            $customerEmail = $xml->createElement("customer_email");
            $customerEmail->appendChild($xml->createCDATASection($order->getCustomerEmail()));
            $element->appendChild($customerEmail);

            $created = $xml->createElement("created");
            $created->appendChild($xml->createCDATASection($order->getOrderCreated()));
            $element->appendChild($created);

            $accept = $xml->createElement("accept_newsletters");
            $accept->appendChild($xml->createCDATASection((int) $order->getAcceptNewsletters()));
            $element->appendChild($accept);

            $products = $xml->createElement("products");

            foreach ($order->getOrderProducts() as $product) {
                $productNode = $xml->createElement("product");

                $id = $xml->createElement("id");
                $id->appendChild($xml->createCDATASection($product->getId()));
                $productNode->appendChild($id);

                $name = $xml->createElement("name");
                $name->appendChild($xml->createCDATASection($product->getName()));
                $productNode->appendChild($name);

                $manufacturer = $xml->createElement("manufacturer");
                $manufacturer->appendChild($xml->createCDATASection($product->getManufacturer()));
                $productNode->appendChild($manufacturer);

                $maincategory = $xml->createElement("maincategory");
                $maincategory->appendChild($xml->createCDATASection($product->getMaincategory()));
                $productNode->appendChild($maincategory);

                $price = $xml->createElement("price_item");
                $price->appendChild($xml->createCDATASection($product->getPrice()));
                $productNode->appendChild($price);

                $priceSum = $xml->createElement("price_sum");
                $priceSum->appendChild($xml->createCDATASection($product->getPriceSum()));
                $productNode->appendChild($priceSum);

                $count = $xml->createElement("count");
                $count->appendChild($xml->createCDATASection($product->getCount()));
                $productNode->appendChild($count);

                $products->appendChild($productNode);
            }
            $element->appendChild($products);
        }
        return $xml->saveXML();
    }

}