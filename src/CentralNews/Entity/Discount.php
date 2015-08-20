<?php

namespace CentralNews\Entity;

class Discount
{
    protected $code = '';
    protected $value = '';
    protected $validityDay = 0;
    protected $minOrderValue = '';

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getValidityDay()
    {
        return $this->validityDay;
    }

    public function setValidityDay($validityDay)
    {
        $this->validityDay = $validityDay;
        return $this;
    }

    public function getMinOrderValue()
    {
        return $this->minOrderValue;
    }

    public function setMinOrderValue($minOrderValue)
    {
        $this->minOrderValue = $minOrderValue;
        return $this;
    }

}