<?php

namespace Base\Services\Payments\Przelewy24;

class TransactionVerify extends AbstractObject
{
    public $merchantId;
    
    public $posId;
    
    public $sessionId;
    
    public $amount;
    
    public $currency;
    
    public $orderId;
    
    public $sign;
    
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
    
    /**
     * Pobierz string dla sumy kontrolnej (sign)
     * @return sring
     */
    public function getSignString($crc)
    {
        $sign = [
            'sessionId' => $this->getSessionId(),
            'orderId' => $this->getOrderId(),
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'crc' => trim($crc),
        ];
        
        return hash('sha384', json_encode($sign, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function validateSign($crc, $signHash)
    {
        return $this->getSignString($crc) === $signHash;
    }
}

