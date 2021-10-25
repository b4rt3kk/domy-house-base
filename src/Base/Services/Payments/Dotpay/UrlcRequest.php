<?php

namespace Base\Services\Payments\Dotpay;

class UrlcRequest extends \Base\Services\Payments\AbstractInput
{
    const OPERATION_STATUS_NEW = 'new';
    const OPERATION_STATUS_PROCESSING = 'processing';
    const OPERATION_STATUS_COMPLETED = 'completed';
    const OPERATION_STATUS_REJECTED = 'rejected';
    const OPERATION_PROCESSING_REALIZATION_WAITING = 'processing_realization_waiting';
    const OPERATION_PROCESSING_REALIZATION = 'processing_realization';
    
    protected $id;
    
    protected $operationNumber;
    
    protected $operationType;
    
    protected $operationStatus;
    
    protected $operationAmount;
    
    protected $operationCurrency;
    
    protected $operationWithdrawalAmount;
    
    protected $operationCommisionAmount;
    
    protected $isCompleted;
    
    protected $operationOriginalAmount;
    
    protected $operationOriginalCurrency;
    
    protected $operationDatetime;
    
    protected $operationRelatedNumber;
    
    protected $control;
    
    protected $description;
    
    protected $email;
    
    protected $pInfo;
    
    protected $pEmail;
    
    protected $creditCardIssuerIdentificationNumber;
    
    protected $creditCardMaskedNumber;
    
    protected $creditCardExpirationYear;
    
    protected $creditCardExpirationMonth;
    
    protected $creditCardBrandCodename;
    
    protected $creditCardBrandCode;
    
    protected $creditCardUniqueIdentifier;
    
    protected $creditCardId;
    
    protected $channel;
    
    protected $channelCountry;
    
    protected $geoipCountry;
    
    protected $payerBankAccountName;
    
    protected $payerBankAccount;
    
    protected $payerTransferTitle;
    
    protected $blikVoucherPin;
    
    protected $blikVoucherAmount;
    
    protected $blikVoucherAmountUsed;
    
    protected $channelReferenceId;
    
    protected $operationSellerCode;
    
    protected $signature;
    
    public function getId()
    {
        return $this->id;
    }

    public function getOperationNumber()
    {
        return $this->operationNumber;
    }

    public function getOperationType()
    {
        return $this->operationType;
    }

    public function getOperationStatus()
    {
        return $this->operationStatus;
    }

    public function getOperationAmount()
    {
        return $this->operationAmount;
    }

    public function getOperationCurrency()
    {
        return $this->operationCurrency;
    }

    public function getOperationWithdrawalAmount()
    {
        return $this->operationWithdrawalAmount;
    }

    public function getOperationCommisionAmount()
    {
        return $this->operationCommisionAmount;
    }

    public function getIsCompleted()
    {
        return $this->isCompleted;
    }

    public function getOperationOriginalAmount()
    {
        return $this->operationOriginalAmount;
    }

    public function getOperationOriginalCurrency()
    {
        return $this->operationOriginalCurrency;
    }

    public function getOperationDatetime()
    {
        return $this->operationDatetime;
    }

    public function getOperationRelatedNumber()
    {
        return $this->operationRelatedNumber;
    }

    public function getControl()
    {
        return $this->control;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPInfo()
    {
        return $this->pInfo;
    }

    public function getPEmail()
    {
        return $this->pEmail;
    }

    public function getCreditCardIssuerIdentificationNumber()
    {
        return $this->creditCardIssuerIdentificationNumber;
    }

    public function getCreditCardMaskedNumber()
    {
        return $this->creditCardMaskedNumber;
    }

    public function getCreditCardExpirationYear()
    {
        return $this->creditCardExpirationYear;
    }

    public function getCreditCardExpirationMonth()
    {
        return $this->creditCardExpirationMonth;
    }

    public function getCreditCardBrandCodename()
    {
        return $this->creditCardBrandCodename;
    }

    public function getCreditCardBrandCode()
    {
        return $this->creditCardBrandCode;
    }

    public function getCreditCardUniqueIdentifier()
    {
        return $this->creditCardUniqueIdentifier;
    }

    public function getCreditCardId()
    {
        return $this->creditCardId;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function getChannelCountry()
    {
        return $this->channelCountry;
    }

    public function getGeoipCountry()
    {
        return $this->geoipCountry;
    }

    public function getPayerBankAccountName()
    {
        return $this->payerBankAccountName;
    }

    public function getPayerBankAccount()
    {
        return $this->payerBankAccount;
    }

    public function getPayerTransferTitle()
    {
        return $this->payerTransferTitle;
    }

    public function getBlikVoucherPin()
    {
        return $this->blikVoucherPin;
    }

    public function getBlikVoucherAmount()
    {
        return $this->blikVoucherAmount;
    }

    public function getBlikVoucherAmountUsed()
    {
        return $this->blikVoucherAmountUsed;
    }

    public function getChannelReferenceId()
    {
        return $this->channelReferenceId;
    }

    public function getOperationSellerCode()
    {
        return $this->operationSellerCode;
    }

    public function getSignature()
    {
        return $this->signature;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function setOperationNumber($operationNumber): void
    {
        $this->operationNumber = $operationNumber;
    }

    public function setOperationType($operationType): void
    {
        $this->operationType = $operationType;
    }

    public function setOperationStatus($operationStatus): void
    {
        $this->operationStatus = $operationStatus;
    }

    public function setOperationAmount($operationAmount): void
    {
        $this->operationAmount = $operationAmount;
    }

    public function setOperationCurrency($operationCurrency): void
    {
        $this->operationCurrency = $operationCurrency;
    }

    public function setOperationWithdrawalAmount($operationWithdrawalAmount): void
    {
        $this->operationWithdrawalAmount = $operationWithdrawalAmount;
    }

    public function setOperationCommisionAmount($operationCommisionAmount): void
    {
        $this->operationCommisionAmount = $operationCommisionAmount;
    }

    public function setIsCompleted($isCompleted): void
    {
        $this->isCompleted = $isCompleted;
    }

    public function setOperationOriginalAmount($operationOriginalAmount): void
    {
        $this->operationOriginalAmount = $operationOriginalAmount;
    }

    public function setOperationOriginalCurrency($operationOriginalCurrency): void
    {
        $this->operationOriginalCurrency = $operationOriginalCurrency;
    }

    public function setOperationDatetime($operationDatetime): void
    {
        $this->operationDatetime = $operationDatetime;
    }

    public function setOperationRelatedNumber($operationRelatedNumber): void
    {
        $this->operationRelatedNumber = $operationRelatedNumber;
    }

    public function setControl($control): void
    {
        $this->control = $control;
    }

    public function setDescription($description): void
    {
        $this->description = $description;
    }

    public function setEmail($email): void
    {
        $this->email = $email;
    }

    public function setPInfo($pInfo): void
    {
        $this->pInfo = $pInfo;
    }

    public function setPEmail($pEmail): void
    {
        $this->pEmail = $pEmail;
    }

    public function setCreditCardIssuerIdentificationNumber($creditCardIssuerIdentificationNumber): void
    {
        $this->creditCardIssuerIdentificationNumber = $creditCardIssuerIdentificationNumber;
    }

    public function setCreditCardMaskedNumber($creditCardMaskedNumber): void
    {
        $this->creditCardMaskedNumber = $creditCardMaskedNumber;
    }

    public function setCreditCardExpirationYear($creditCardExpirationYear): void
    {
        $this->creditCardExpirationYear = $creditCardExpirationYear;
    }

    public function setCreditCardExpirationMonth($creditCardExpirationMonth): void
    {
        $this->creditCardExpirationMonth = $creditCardExpirationMonth;
    }

    public function setCreditCardBrandCodename($creditCardBrandCodename): void
    {
        $this->creditCardBrandCodename = $creditCardBrandCodename;
    }

    public function setCreditCardBrandCode($creditCardBrandCode): void
    {
        $this->creditCardBrandCode = $creditCardBrandCode;
    }

    public function setCreditCardUniqueIdentifier($creditCardUniqueIdentifier): void
    {
        $this->creditCardUniqueIdentifier = $creditCardUniqueIdentifier;
    }

    public function setCreditCardId($creditCardId): void
    {
        $this->creditCardId = $creditCardId;
    }

    public function setChannel($channel): void
    {
        $this->channel = $channel;
    }

    public function setChannelCountry($channelCountry): void
    {
        $this->channelCountry = $channelCountry;
    }

    public function setGeoipCountry($geoipCountry): void
    {
        $this->geoipCountry = $geoipCountry;
    }

    public function setPayerBankAccountName($payerBankAccountName): void
    {
        $this->payerBankAccountName = $payerBankAccountName;
    }

    public function setPayerBankAccount($payerBankAccount): void
    {
        $this->payerBankAccount = $payerBankAccount;
    }

    public function setPayerTransferTitle($payerTransferTitle): void
    {
        $this->payerTransferTitle = $payerTransferTitle;
    }

    public function setBlikVoucherPin($blikVoucherPin): void
    {
        $this->blikVoucherPin = $blikVoucherPin;
    }

    public function setBlikVoucherAmount($blikVoucherAmount): void
    {
        $this->blikVoucherAmount = $blikVoucherAmount;
    }

    public function setBlikVoucherAmountUsed($blikVoucherAmountUsed): void
    {
        $this->blikVoucherAmountUsed = $blikVoucherAmountUsed;
    }

    public function setChannelReferenceId($channelReferenceId): void
    {
        $this->channelReferenceId = $channelReferenceId;
    }

    public function setOperationSellerCode($operationSellerCode): void
    {
        $this->operationSellerCode = $operationSellerCode;
    }

    public function setSignature($signature): void
    {
        $this->signature = $signature;
    }
    
    /**
     * Sprawdź czy sygnatura przesłanych danych jest poprawna
     * @param string $pin
     * @return bool
     */
    public function isSignatureValid($pin)
    {
        $string = $pin . 
                $this->getId() .
                $this->getOperationNumber() .
                $this->getOperationType() .
                $this->getOperationStatus() .
                $this->getOperationAmount() .
                $this->getOperationCurrency() .
                $this->getOperationWithdrawalAmount() .
                $this->getOperationCommisionAmount() .
                $this->getIsCompleted() .
                $this->getOperationOriginalAmount() .
                $this->getOperationOriginalCurrency() .
                $this->getOperationDatetime() .
                $this->getOperationRelatedNumber() .
                $this->getControl() .
                $this->getDescription() .
                $this->getEmail() .
                $this->getPInfo() .
                $this->getPEmail() .
                $this->getCreditCardIssuerIdentificationNumber() .
                $this->getCreditCardMaskedNumber() .
                $this->getCreditCardExpirationYear() .
                $this->getCreditCardExpirationMonth() .
                $this->getCreditCardBrandCodename() .
                $this->getCreditCardBrandCode() . 
                $this->getCreditCardUniqueIdentifier() .
                $this->getCreditCardId() .
                $this->getChannel() .
                $this->getChannelCountry() .
                $this->getGeoipCountry() .
                $this->getPayerBankAccountName() .
                $this->getPayerBankAccount() .
                $this->getPayerTransferTitle() .
                $this->getBlikVoucherPin() .
                $this->getBlikVoucherAmount() .
                $this->getBlikVoucherAmountUsed() .
                $this->getChannelReferenceId() .
                $this->getOperationSellerCode();
        
        $countedSignature = hash('sha256', $string);
        $signature = $this->getSignature();
        
        return $countedSignature === $signature;
    }
}