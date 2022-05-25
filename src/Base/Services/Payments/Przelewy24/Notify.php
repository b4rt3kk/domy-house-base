<?php

namespace Base\Services\Payments\Przelewy24;

class Notify extends AbstractObject
{
    protected $merchantId;
    
    protected $posId;
    
    protected $sessionId;
    
    protected $amount;
    
    protected $originAmount;
    
    protected $currency;
    
    protected $orderId;
    
    protected $methodId;
    
    protected $statement;
    
    protected $sign;
    
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    public function getPosId()
    {
        return $this->posId;
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getOriginAmount()
    {
        return $this->originAmount;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getMethodId()
    {
        return $this->methodId;
    }

    public function getStatement()
    {
        return $this->statement;
    }

    public function getSign()
    {
        return $this->sign;
    }

    public function setMerchantId($merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    public function setPosId($posId): void
    {
        $this->posId = $posId;
    }

    public function setSessionId($sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    public function setOriginAmount($originAmount): void
    {
        $this->originAmount = $originAmount;
    }

    public function setCurrency($currency): void
    {
        $this->currency = $currency;
    }

    public function setOrderId($orderId): void
    {
        $this->orderId = $orderId;
    }

    public function setMethodId($methodId): void
    {
        $this->methodId = $methodId;
    }

    public function setStatement($statement): void
    {
        $this->statement = $statement;
    }

    public function setSign($sign): void
    {
        $this->sign = $sign;
    }
}

