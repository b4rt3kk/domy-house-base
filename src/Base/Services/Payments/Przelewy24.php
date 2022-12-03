<?php

namespace Base\Services\Payments;

class Przelewy24 extends AbstractPayment
{
    protected $code = 'przelewy24';
    
    protected $merchantId;
    
    protected $posId;
    
    protected $sessionId;
    
    protected $amount;
    
    protected $currency;
    
    protected $description;
    
    protected $email;
    
    protected $client;
    
    protected $address;
    
    protected $zip;
    
    protected $city;
    
    protected $country;
    
    protected $phone;
    
    protected $language;
    
    protected $method;
    
    protected $urlReturn;
    
    protected $urlStatus;
    
    protected $timeLimit;
    
    protected $channel;
    
    protected $waitForResult;
    
    protected $regulationAccept;
    
    protected $shipping;
    
    protected $transferLabel;
    
    protected $mobileLib;
    
    protected $sdkVersion;
    
    protected $sign;
    
    protected $encoding;
    
    protected $methodRefId;
    
    protected $cart;
    
    protected $additional;
    
    protected $orderId;
    
    protected $originAmount;
    
    protected $methodId;
    
    protected $statement;
    
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
        return (string) $this->sessionId;
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
        $this->merchantId = (int) $merchantId;
    }

    public function setPosId($posId): void
    {
        $this->posId = (int) $posId;
    }

    public function setSessionId($sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function setAmount($amount): void
    {
        $this->amount = (int) $amount;
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
        $this->method = (int) $method;
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
        $this->timeLimit = (int) $timeLimit;
    }

    public function setChannel($channel): void
    {
        $this->channel = (int) $channel;
    }

    public function setWaitForResult($waitForResult): void
    {
        $this->waitForResult = !empty($waitForResult);
    }

    public function setRegulationAccept($regulationAccept): void
    {
        $this->regulationAccept = !empty($regulationAccept);
    }

    public function setShipping($shipping): void
    {
        $this->shipping = (int) $shipping;
    }

    public function setTransferLabel($transferLabel): void
    {
        $this->transferLabel = $transferLabel;
    }

    public function setMobileLib($mobileLib): void
    {
        $this->mobileLib = (int) $mobileLib;
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
    
    public function getOrderId()
    {
        return $this->orderId;
    }

    public function setOrderId($orderId): void
    {
        $this->orderId = $orderId;
    }
    
    public function getOriginAmount()
    {
        return $this->originAmount;
    }

    public function getMethodId()
    {
        return $this->methodId;
    }

    public function getStatement()
    {
        return $this->statement;
    }

    public function setOriginAmount($originAmount): void
    {
        $this->originAmount = $originAmount;
    }

    public function setMethodId($methodId): void
    {
        $this->methodId = $methodId;
    }

    public function setStatement($statement): void
    {
        $this->statement = $statement;
    }

    /**
     * Pobierz url do dokonania płatności w serwisie przelewy24
     * @return string
     * @throws \Exception
     */
    public function getCheckoutPaymentUrl()
    {
        $response = $this->transactionRegister();
        
        $token = $response->data->token;
        
        if (empty($token)) {
            throw new \Exception("Nie udało się pobrać tokenu dla transakcji");
        }
        
        $targetUrl = trim($this->getConfigValue('target_url'), '/');
        
        $url = $targetUrl . '/trnRequest/' . $token;
        
        return $url;
    }
        
    /**
     * Zarejestruj nową transakcję
     * @return \stdClass
     * @throws \Exception
     */
    public function transactionRegister()
    {
        $targetUrl = $this->getConfigValue('target_url');
        
        $params = [
            "merchantId",
            "posId",
            "sessionId",
            "amount",
            "currency",
            "description",
            "email",
            "client",
            "address",
            "zip",
            "city",
            "country",
            "phone",
            "language",
            "method",
            "urlReturn",
            "urlStatus",
            "timeLimit",
            "channel",
            "waitForResult",
            "regulationAccept",
            "shipping",
            "transferLabel",
            "mobileLib",
            "sdkVersion",
            "sign",
            "encoding",
            "methodRefId",
            "cart",
            "additional",
        ];
        
        $client = $this->getHttpClient();
        $client->setUri($targetUrl . '/api/v1/transaction/register');
        $client->setHeaders(['Content-Type:application/json']);
        
        $adapter = $client->getAdapter();
        /* @var $adapter \Laminas\Http\Client\Adapter\Curl */
        $adapter->setCurlOption(CURLOPT_POST, 1);
        
        $input = new Przelewy24\TransactionRegister();
        $input->setData($this->getParamsData($params));
        $input->setSign($input->getSignString(trim($this->getConfigValue('crc'))));
        
        $inputObject = $input->getDataObject();
        
        // treść przesłanego body
        $adapter->setCurlOption(CURLOPT_POSTFIELDS, json_encode($inputObject, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        
        $response = $client->send();
        
        $body = json_decode($response->getBody());
        
        if (!empty($body->error)) {
            if ($this->getIsSandbox()) {
                throw new \Exception(sprintf("Wystąpił błąd płatności: %s", $body->error));
            }
            
            throw new \Exception("Wystąpił błąd płatności");
        }
        
        return $body;
    }
    
    /**
     * Zweryfikuj transakcję
     * @return \stdClass
     * @throws \Exception
     */
    public function transactionVerify()
    {
        $logger = $this->getServiceManager()->get(\Base\Logger\Logger::class);
        /* @var $logger \Base\Logger\Logger */
        $targetUrl = $this->getConfigValue('target_url');
        
        $params = [
            'merchantId',
            'posId',
            'sessionId',
            'amount',
            'currency',
            'orderId',
            'sign',
        ];
        
        $logger->logMessage("VERIFY TARGET URL " . $targetUrl . '/api/v1/transaction/verify');
        
        $client = $this->getHttpClient();
        $client->setUri($targetUrl . '/api/v1/transaction/verify');
        $client->setHeaders(['Content-Type:application/json']);
        $client->setMethod(\Laminas\Http\Request::METHOD_PUT);
        
        $adapter = $client->getAdapter();
        /* @var $adapter \Laminas\Http\Client\Adapter\Curl */
        $adapter->setCurlOption(CURLOPT_POST, true);
        $adapter->setCurlOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $adapter->setCurlOption(CURLOPT_CUSTOMREQUEST, \Laminas\Http\Request::METHOD_PUT);
        
        $input = new Przelewy24\TransactionVerify();
        $input->setData($this->getParamsData($params));
        $input->setSign($input->getSignString(trim($this->getConfigValue('crc'))));
        
        $inputObject = $input->getDataObject();
        
        $logger->logMessage("VERIFY OBJECT " . serialize($inputObject));
        
        $adapter->setCurlOption(CURLOPT_POSTFIELDS, json_encode($inputObject, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        
        // treść przesłanego body
        //$adapter->setCurlOption(CURLOPT_POSTFIELDS, json_encode($inputObject, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $client->setRawBody(json_encode($inputObject, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        
        $response = $client->send();
        
        $logger->logMessage("VERIFY OBJECT REQUEST " . serialize($client->getLastRawRequest()));
        $logger->logMessage("VERIFY OBJECT RESPONSE " . serialize($response));
        $logger->logMessage("VERIFY OBJECT STATUS " . $response->getStatusCode());
        
        $body = json_decode($response->getBody());
        /*
        if (!empty($body->error)) {
            if ($this->getIsSandbox()) {
                throw new \Exception(sprintf("Wystąpił błąd płatności: %s, %s %s", $body->error, serialize($this->getParamsData($params)), serialize($inputObject)));
            }
            
            throw new \Exception("Wystąpił błąd płatności");
        }
        */
        return $body;
    }
    
    /**
     * Odbierz dane transakcji
     * @return \Base\Services\Payments\Przelewy24\Notify
     */
    public function getNotify()
    {
        $params = [
            "merchantId",
            "posId",
            "sessionId",
            "amount",
            "originAmount",
            "currency",
            "orderId",
            "methodId",
            "statement",
            "sign",
        ];
        
        $input = new Przelewy24\Notify();
        $input->setData($this->getParamsData($params));
        
        if (!$input->validateSign(trim($this->getConfigValue('crc')))) {
            throw new \Exception("Nieprawidłowa suma kontrolna");
        }
        
        return $input;
    }
    
    public function isLandingPagePaymentSuccess($params)
    {
        ;
    }
    
    public function updatePaymentData()
    {
        ;
    }
    
    public function afterConfirmationDataRecieved()
    {
        ;
    }
    
    /**
     * Pobierz klienta do wykonywania metod REST
     * @return \Laminas\Http\Client
     */
    protected function getHttpClient()
    {
        $user = $this->getConfigValue('merchant_id');
        $password = $this->getConfigValue('secret_id');
        
        $adapter = new \Laminas\Http\Client\Adapter\Curl();
        $adapter->setCurlOption(CURLOPT_HEADER, true);
        $adapter->setCurlOption(CURLOPT_RETURNTRANSFER, true);
        $adapter->setCurlOption(CURLOPT_REFERER, '');
        $adapter->setCurlOption(CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2');
        $adapter->setCurlOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $adapter->setCurlOption(CURLOPT_USERPWD, "{$user}:{$password}");
        
        $client = new \Laminas\Http\Client();
        $client->setAdapter($adapter);
        $client->setOptions([
            'timeout' => 0,
        ]);

        return $client;
    }
    
    public function rawCurlTransactionVerify()
    {
        $logger = $this->getServiceManager()->get(\Base\Logger\Logger::class);
        /* @var $logger \Base\Logger\Logger */
        
        $user = $this->getConfigValue('merchant_id');
        $password = $this->getConfigValue('secret_id');
        
        $params = [
            'merchantId',
            'posId',
            'sessionId',
            'amount',
            'currency',
            'orderId',
            'sign',
        ];
        
        $targetUrl = $this->getConfigValue('target_url') . '/api/v1/transaction/verify';

        $curl = curl_init($targetUrl);
        curl_setopt($curl, CURLOPT_URL, $targetUrl);
        //curl_setopt($curl, CURLOPT_PUT, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        $headers = [
            "Content-Type: application/json",
        ];
        
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "{$user}:{$password}");
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_POST, true);
        
        $input = new Przelewy24\TransactionVerify();
        $input->setData($this->getParamsData($params));
        $input->setSign($input->getSignString(trim($this->getConfigValue('crc'))));
        
        $inputObject = $input->getDataObject();

        $data = json_encode($inputObject, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        
        //var_dump(curl_error($curl), curl_errno($curl));
        
        curl_close($curl);
        
        $logger->logMessage("RESPONSE " . serialize($resp));
    }
}
