<?php

namespace CentralNews\Model;

use CentralNews\Entity\LostCart;
use CentralNews\Entity\Discount;

class LostCartManager extends Manager
{
    const BUY_TEXT = 'Koupit';

    protected $debug = false;

    public function sendCart(LostCart $lostCart)
    {
        $orderXml = $this->getXml($lostCart);
        $encodedXmlData = base64_encode($orderXml);

        $timestamp = null;
        if ($this->getDebug()) {
            $timestamp = microtime();
        }

        $param = array(
            'email' => $lostCart->getEmail(),
            'content' => $encodedXmlData,
            'event' => 'lost_carts',
            'code' => $lostCart->getEmail() . '-' . date("d.m.Y") . $timestamp,
        );

        $request = new \CentralNews\Service\Request('user_event', $param, '', '');
        return $this->sendRequest($request);
    }

    protected function getXml(LostCart $lostCart)
    {
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement("event");

        if ($lostCart->getLostCartUrl()) {

            $xml->writeAttribute('RESTORE_CART_URL', $lostCart->getLostCartUrl());
            // slevovy kupon
            $discountCoupon = $lostCart->getDiscountCoupon();
            if ($discountCoupon instanceof Discount) {
                $xml->writeAttribute('COUPON_CODE', $discountCoupon->getCode());
                $xml->writeAttribute('COUPON_VALUE', $discountCoupon->getValue());
                $xml->writeAttribute('COUPON_VALIDITY', $this->dayToDate($discountCoupon->getValidityDay()));
                $xml->writeAttribute('COUPON_ORDER_VALUE', $discountCoupon->getMinOrderValue());
            }
        }

        $xml->startElement("products");
        foreach ($lostCart->getProducts() as $product) {

            $xml->startElement("product");

            $xml->startElement("name");
            $xml->writeCData($product->getName());
            $xml->endElement();

            $xml->startElement("url");
            $xml->writeCData($product->getUrl());
            $xml->endElement();

            $xml->startElement("img");
            $xml->writeCData($product->getImage());
            $xml->endElement();

            $xml->startElement("price-old");
            $xml->writeCData($product->getPriceOld());
            $xml->endElement();

            $xml->startElement("price");
            $xml->writeCData($product->getPrice());
            $xml->endElement();

            $xml->startElement("sale");
            $xml->writeCData($product->getSale());
            $xml->endElement();

            $xml->startElement("buy-text");
            $xml->writeCData(self::BUY_TEXT);
            $xml->endElement();

            $xml->endElement();
        }
        $xml->endElement();

        $xml->endElement();
        $xml->endDocument();

        return $xml->flush();
    }

    protected function dayToDate($day)
    {
        return strftime('%d.%m.%Y', time() + ($day * 86400));
    }

    public function getDebug()
    {
        return $this->debug;
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

}