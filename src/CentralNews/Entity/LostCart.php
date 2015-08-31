<?php

namespace CentralNews\Entity;

use CentralNews\Exception\DomainException;
use CentralNews\Exception\InvalidArgumentException;
use CentralNews\Service\Client;

class LostCart
{
    protected $lostCartUrl = '';
    
    /** @var string */
    protected $email;
    
    /** @var array|null */
    protected $products;
    
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

    /**
     * @return string
     * @throws \CentralNews\Exception\DomainException
     */
    public function getEmail()
    {
        if (!$this->email) {
            throw new DomainException('Email is not set');
        }
        return $this->email;
    }

    /**
     * 
     * @param type $email
     * @return \CentralNews\Entity\LostCart
     * @throws \CentralNews\Exception\InvalidArgumentException
     */
    public function setEmail($email)
    {
        if (!Client::isEmail($email)) {
            throw new InvalidArgumentException('Invalid email');
        }
        $this->email = $email;
        return $this;
    }

    public function getProducts()
    {
        if (empty($this->products)) {
            throw new DomainException('Products is not set');
        }
        return $this->products;
    }

    public function addProduct(Product $product)
    {
        $this->products[] = $product;
        return $this;
    }

    public function setProducts(array $products)
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