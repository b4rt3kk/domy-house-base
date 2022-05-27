<?php

namespace Base\Services\Payments\Przelewy24;

class TransactionRegister extends AbstractObject
{
    public $merchantId;
    
    public $posId;
    
    public $sessionId;
    
    public $amount;
    
    public $currency;
    
    public $description;
    
    public $email;
    
    public $client;
    
    public $address;
    
    public $zip;
    
    public $city;
    
    public $country;
    
    public $phone;
    
    public $language;
    
    public $method;
    
    public $urlReturn;
    
    public $urlStatus;
    
    public $timeLimit;
    
    public $channel;
    
    public $waitForResult;
    
    public $regulationAccept;
    
    public $shipping;
    
    public $transferLabel;
    
    public $mobileLib;
    
    public $sdkVersion;
    
    public $sign;
    
    public $encoding;
    
    public $methodRefId;
    
    public $cart;
    
    public $additional;

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

    public function getDescription()
    {
        return $this->description;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getZip()
    {
        return $this->zip;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getUrlReturn()
    {
        return $this->urlReturn;
    }

    public function getUrlStatus()
    {
        return $this->urlStatus;
    }

    public function getTimeLimit()
    {
        return $this->timeLimit;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function getWaitForResult()
    {
        return $this->waitForResult;
    }

    public function getRegulationAccept()
    {
        return $this->regulationAccept;
    }

    public function getShipping()
    {
        return $this->shipping;
    }

    public function getTransferLabel()
    {
        return $this->transferLabel;
    }

    public function getMobileLib()
    {
        return $this->mobileLib;
    }

    public function getSdkVersion()
    {
        return $this->sdkVersion;
    }

    public function getSign()
    {
        return $this->sign;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    public function getMethodRefId()
    {
        return $this->methodRefId;
    }

    public function getCart()
    {
        return $this->cart;
    }

    public function getAdditional()
    {
        return $this->additional;
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

    public function setDescription($description): void
    {
        $this->description = $description;
    }

    public function setEmail($email): void
    {
        $this->email = $email;
    }

    public function setClient($client): void
    {
        $this->client = $client;
    }

    public function setAddress($address): void
    {
        $this->address = $address;
    }

    public function setZip($zip): void
    {
        $this->zip = $zip;
    }

    public function setCity($city): void
    {
        $this->city = $city;
    }

    public function setCountry($country): void
    {
        $this->country = $country;
    }

    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    public function setLanguage($language): void
    {
        $this->language = $language;
    }

    public function setMethod($method): void
    {
        $this->method = $method;
    }

    public function setUrlReturn($urlReturn): void
    {
        $this->urlReturn = $urlReturn;
    }

    public function setUrlStatus($urlStatus): void
    {
        $this->urlStatus = $urlStatus;
    }

    public function setTimeLimit($timeLimit): void
    {
        $this->timeLimit = $timeLimit;
    }

    public function setChannel($channel): void
    {
        $this->channel = $channel;
    }

    public function setWaitForResult($waitForResult): void
    {
        $this->waitForResult = $waitForResult;
    }

    public function setRegulationAccept($regulationAccept): void
    {
        $this->regulationAccept = $regulationAccept;
    }

    public function setShipping($shipping): void
    {
        $this->shipping = $shipping;
    }

    public function setTransferLabel($transferLabel): void
    {
        $this->transferLabel = $transferLabel;
    }

    public function setMobileLib($mobileLib): void
    {
        $this->mobileLib = $mobileLib;
    }

    public function setSdkVersion($sdkVersion): void
    {
        $this->sdkVersion = $sdkVersion;
    }

    public function setSign($sign): void
    {
        $this->sign = $sign;
    }

    public function setEncoding($encoding): void
    {
        $this->encoding = $encoding;
    }

    public function setMethodRefId($methodRefId): void
    {
        $this->methodRefId = $methodRefId;
    }

    public function setCart($cart): void
    {
        $this->cart = $cart;
    }

    public function setAdditional($additional): void
    {
        $this->additional = $additional;
    }
    
    /**
     * Pobierz string dla sumy kontrolnej (sign)
     * @return sring
     */
    public function getSignString($crc)
    {
        $sign = [
            'sessionId' => $this->getSessionId(),
            'merchantId' => $this->getMerchantId(),
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

