<?php

namespace CentralNews\Entity;

class LostCart
{
    protected $lostCartUrl = '';
    protected $email = '';
    protected $products = array();
    protected $discountCoupon = null;

    public function getLostCartUrl()
    {
        return $this->lostCartUrl;
    }

    public function setLostCartUrl($lostCartUrl)
    {
        $this->lostCartUrl = $lostCartUrl;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function addProduct(Product $product)
    {
        $this->products[] = $product;
        return $this;
    }

    public function setProducts($products)
    {
        $this->products = $products;
        return $this;
    }

    public function getDiscountCoupon()
    {
        return $this->discountCoupon;
    }

    public function setDiscountCoupon(Discount $discountCoupon)
    {
        $this->discountCoupon = $discountCoupon;
        return $this;
    }

}