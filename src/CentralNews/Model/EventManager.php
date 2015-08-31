<?php

namespace CentralNews\Model;

use CentralNews\Entity\LostCart;
use CentralNews\Entity\Discount;
use CentralNews\Service\Request;

class EventManager extends Manager
{
    /** @var bool */
    protected $debugMode = false;

    /** @var string */
    protected $buyText = 'Koupit';

    /**
     * @param bool $debugMode
     */
    public function setDebugMode($debugMode = false)
    {
        $this->debugMode = (bool) $debugMode;
    }

    /**
     * @param string $buyText
     */
    public function setBuyText($buyText)
    {
        $this->buyText = (string) $buyText;
    }

    /**
     * @return string
     */
    public function getBuyText()
    {
        return $this->buyText;
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->debugMode;
    }

    /**
     * @param \CentralNews\Entity\LostCart $lostCart
     * @return bool
     */
    public function callLostCart(LostCart $lostCart, $idenrificator = null)
    {
        $orderXml = $this->getXmlLostCart($lostCart);
        $code = $idenrificator ? $idenrificator : $lostCart->getEmail() . '-' . date("d.m.Y");

        $param = array(
            'email' => $lostCart->getEmail(),
            'content' => base64_encode($orderXml),
            'event' => 'lost_carts',
            'code' => $code . ($this->isDebugMode() ? '#' . microtime() : ''),
            'bulk' => '',
        );

        $request = new Request('user_event', $param, '', '');
        $response = $this->sendRequest($request);

        return $response->isSuccess();
    }

    protected function getXmlLostCart(LostCart $lostCart)
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
            $xml->writeCData($this->getBuyText());
            $xml->endElement();

            $xml->endElement();
        }
        $xml->endElement();

        $xml->endElement();
        $xml->endDocument();

        return $xml->flush();
    }

}