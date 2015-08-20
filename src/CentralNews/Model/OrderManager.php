<?php

namespace CentralNews\Model;

use CentralNews\Service\Client;
use CentralNews\Service\Response;
use CentralNews\Entity\Order;

class OrderManager extends Manager
{
    protected $centralNewsApi = null;
    protected $orders = array();

    public function __construct(Client $centralNewsApi)
    {
        $this->centralNewsApi = $centralNewsApi;
    }

    public function sendOrders()
    {
        $xmlOrders = $this->getXml();
        $encodedXmlData = base64_encode($xmlOrders);

        $param = array(
            'group_id' => $this->getIdGroup(),
            'orders' => $encodedXmlData
        );

        $rawResponse = $this->centralNewsApi->createApiClient()->call('import_orders', $param, '', '', $this->centralNewsApi->getSoapHeaders());
        return new Response($rawResponse);
    }

    protected function getXml()
    {
        $xml = new \DOMDocument();
        $xml->formatOutput = true;
        $root = $xml->appendChild($xml->createElement("orders"));

        foreach($this->getOrders() as $order) {
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
            $accept->appendChild($xml->createCDATASection($order->getAcceptNewsletters()));
            $element->appendChild($accept);

            $products = $xml->createElement("products");

            foreach($order->getOrderProducts() as $product) {

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

    public function getOrders()
    {
        return $this->orders;
    }

    public function setOrders($orders)
    {
        $this->orders = $orders;
        return $this;
    }

    public function addOrder(Order $order)
    {
        $this->orders[] = $order;
        return $this;
    }

}