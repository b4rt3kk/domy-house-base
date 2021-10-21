<?php

namespace Base\Services\Payments;

class Dotpay extends AbstractPayment
{
    protected $code = 'dotpay';
    
    protected $params = [
        'api_version',
        'id',
        'amount',
        'currency',
        'description',
        'chk',
        'channel',
        'ch_lock',
        'ignore_last_payment_channel',
        'channel_groups',
        'url',
        'type',
        'buttontext',
        'bylaw',
        'personal_data',
        'urlc',
        'expiration_date',
        'control',
        'firstname',
        'lastname',
        'email',
        'street',
        'street_n1',
        'street_n2',
        'state',
        'addr3',
        'city',
        'postcode',
        'phone',
        'country',
        'lang',
        'customer',
        'deladdr',
        'p_info',
        'p_email',
        'pid',
        'blik_code',
        'gp_token',
        'ap_token',
    ];
    
    protected $apiVersion;
    
    protected $id;
    
    protected $amount;
    
    protected $currency;
    
    protected $description;
    
    protected $chk;
    
    protected $channel;
    
    protected $chLock;
    
    protected $ignoreLastPaymentChannel;
    
    protected $channelGroups;
    
    protected $url;
    
    protected $type;
    
    protected $buttontext;
    
    protected $bylaw;
    
    protected $personalData;
    
    protected $urlc;
    
    protected $expirationDate;
    
    protected $control;
    
    protected $firstname;
    
    protected $lastname;
    
    protected $email;
    
    protected $street;
    
    protected $streetN1;
    
    protected $streetN2;
    
    protected $state;
    
    protected $addr3;
    
    protected $city;
    
    protected $postcode;
    
    protected $phone;
    
    protected $country;
    
    protected $lang;
    
    protected $customer;
    
    protected $deladdr;
    
    protected $pInfo;
    
    protected $pEmail;
    
    protected $pid;
    
    protected $blikCode;
    
    protected $gpToken;
    
    protected $apToken;
    
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    public function getId()
    {
        return $this->id;
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

    public function getChk()
    {
        return $this->chk;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function getChLock()
    {
        return $this->chLock;
    }

    public function getIgnoreLastPaymentChannel()
    {
        return $this->ignoreLastPaymentChannel;
    }

    public function getChannelGroups()
    {
        return $this->channelGroups;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getButtontext()
    {
        return $this->buttontext;
    }

    public function getBylaw()
    {
        return $this->bylaw;
    }

    public function getPersonalData()
    {
        return $this->personalData;
    }

    public function getUrlc()
    {
        return $this->urlc;
    }

    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    public function getControl()
    {
        return $this->control;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function getStreetN1()
    {
        return $this->streetN1;
    }

    public function getStreetN2()
    {
        return $this->streetN2;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getAddr3()
    {
        return $this->addr3;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getPostcode()
    {
        return $this->postcode;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getLang()
    {
        return $this->lang;
    }

    public function getCustomer()
    {
        return $this->customer;
    }

    public function getDeladdr()
    {
        return $this->deladdr;
    }

    public function getPInfo()
    {
        return $this->pInfo;
    }

    public function getPEmail()
    {
        return $this->pEmail;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function getBlikCode()
    {
        return $this->blikCode;
    }

    public function getGpToken()
    {
        return $this->gpToken;
    }

    public function getApToken()
    {
        return $this->apToken;
    }

    public function setApiVersion($apiVersion): void
    {
        $this->apiVersion = $apiVersion;
    }

    public function setId($id): void
    {
        $this->id = $id;
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

    public function setChk($chk): void
    {
        $this->chk = $chk;
    }

    public function setChannel($channel): void
    {
        $this->channel = $channel;
    }

    public function setChLock($chLock): void
    {
        $this->chLock = $chLock;
    }

    public function setIgnoreLastPaymentChannel($ignoreLastPaymentChannel): void
    {
        $this->ignoreLastPaymentChannel = $ignoreLastPaymentChannel;
    }

    public function setChannelGroups($channelGroups): void
    {
        $this->channelGroups = $channelGroups;
    }

    public function setUrl($url): void
    {
        $this->url = $url;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function setButtontext($buttontext): void
    {
        $this->buttontext = $buttontext;
    }

    public function setBylaw($bylaw): void
    {
        $this->bylaw = $bylaw;
    }

    public function setPersonalData($personalData): void
    {
        $this->personalData = $personalData;
    }

    public function setUrlc($urlc): void
    {
        $this->urlc = $urlc;
    }

    public function setExpirationDate($expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    public function setControl($control): void
    {
        $this->control = $control;
    }

    public function setFirstname($firstname): void
    {
        $this->firstname = $firstname;
    }

    public function setLastname($lastname): void
    {
        $this->lastname = $lastname;
    }

    public function setEmail($email): void
    {
        $this->email = $email;
    }

    public function setStreet($street): void
    {
        $this->street = $street;
    }

    public function setStreetN1($streetN1): void
    {
        $this->streetN1 = $streetN1;
    }

    public function setStreetN2($streetN2): void
    {
        $this->streetN2 = $streetN2;
    }

    public function setState($state): void
    {
        $this->state = $state;
    }

    public function setAddr3($addr3): void
    {
        $this->addr3 = $addr3;
    }

    public function setCity($city): void
    {
        $this->city = $city;
    }

    public function setPostcode($postcode): void
    {
        $this->postcode = $postcode;
    }

    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    public function setCountry($country): void
    {
        $this->country = $country;
    }

    public function setLang($lang): void
    {
        $this->lang = $lang;
    }

    public function setCustomer($customer): void
    {
        $this->customer = $customer;
    }

    public function setDeladdr($deladdr): void
    {
        $this->deladdr = $deladdr;
    }

    public function setPInfo($pInfo): void
    {
        $this->pInfo = $pInfo;
    }

    public function setPEmail($pEmail): void
    {
        $this->pEmail = $pEmail;
    }

    public function setPid($pid): void
    {
        $this->pid = $pid;
    }

    public function setBlikCode($blikCode): void
    {
        $this->blikCode = $blikCode;
    }

    public function setGpToken($gpToken): void
    {
        $this->gpToken = $gpToken;
    }

    public function setApToken($apToken): void
    {
        $this->apToken = $apToken;
    }
}
