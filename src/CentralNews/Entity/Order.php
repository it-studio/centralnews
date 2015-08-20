<?php

namespace CentralNews\Entity;

class Order
{
    protected $orderNumber = 0;
    protected $orderTotalPrice = 0;
    protected $customerEmail = '';
    protected $orderCreated = '0000-00-00 00:00:00';
    protected $orderProducts = array();
    protected $acceptNewsletters = 0;

    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function getOrderTotalPrice()
    {
        return $this->orderTotalPrice;
    }

    public function setOrderTotalPrice($orderTotalPrice)
    {
        $this->orderTotalPrice = $orderTotalPrice;
        return $this;
    }

    public function getCustomerEmail()
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail($customerEmail)
    {
        $this->customerEmail = $customerEmail;
        return $this;
    }

    public function getOrderCreated()
    {
        return $this->orderCreated;
    }

    public function setOrderCreated($orderCreated)
    {
        $this->orderCreated = $orderCreated;
        return $this;
    }

    public function getOrderProducts()
    {
        return $this->orderProducts;
    }

    public function setOrderProducts($orderProducts)
    {
        $this->orderProducts = $orderProducts;
        return $this;
    }

    public function addOrderProduct(Product $orderProduct)
    {
        $this->orderProducts[] = $orderProduct;
        return $this;
    }

    public function getAcceptNewsletters()
    {
        return $this->acceptNewsletters;
    }

    public function setAcceptNewsletters($orderAccept)
    {
        $this->acceptNewsletters = $orderAccept;
        return $this;
    }

}