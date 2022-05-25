<?php

namespace Base\Services\Payments\Przelewy24;

class TransactionVerify extends AbstractObject
{
    protected $merchantId;
    
    protected $posId;
    
    protected $sessionId;
    
    protected $amount;
    
    protected $currency;
    
    protected $orderId;
    
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

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getOrderId()
    {
        return $this->orderId;
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

    public function setCurrency($currency): void
    {
        $this->currency = $currency;
    }

    public function setOrderId($orderId): void
    {
        $this->orderId = $orderId;
    }

    public function setSign($sign): void
    {
        $this->sign = $sign;
    }
}

