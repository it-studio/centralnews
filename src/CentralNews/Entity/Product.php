<?php

namespace CentralNews\Entity;

class Product
{
    protected $id;
    protected $name;
    protected $manufacturer;
    protected $maincategory;
    protected $price;
    protected $count;
    protected $url;
    protected $image;
    protected $description;
    protected $priceOld;
    protected $sale;
    protected $heurekaItemId;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
        return $this;
    }

    public function getMaincategory()
    {
        return $this->maincategory;
    }

    public function setMaincategory($maincategory)
    {
        $this->maincategory = $maincategory;
        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getPriceOld()
    {
        return $this->priceOld;
    }

    public function setPriceOld($priceOld)
    {
        $this->priceOld = $priceOld;
        return $this;
    }

    public function getSale()
    {
        return $this->sale;
    }

    public function setSale($sale)
    {
        $this->sale = $sale;
        return $this;
    }
    
    public function getHeurekaItemId() 
    {
        return $this->heurekaItemId;
    }

    public function setHeurekaItemId($heurekaItemId) 
    {
        $this->heurekaItemId = $heurekaItemId;
    }



}