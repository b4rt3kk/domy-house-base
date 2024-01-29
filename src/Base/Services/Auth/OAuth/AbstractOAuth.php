<?php

namespace Base\Services\Auth\OAuth;

abstract class AbstractOAuth extends \Base\Services\Auth\AbstractAuthAdapter
{
    protected $providerColumnName = 'id_oauth_provider';
    
    protected $uidColumnName = 'oauth_uid';
    
    protected $providerId;
    
    public function getProviderColumnName()
    {
        return $this->providerColumnName;
    }

    public function getUidColumnName()
    {
        return $this->uidColumnName;
    }

    public function setProviderColumnName($providerColumnName)
    {
        $this->providerColumnName = $providerColumnName;
    }

    public function setUidColumnName($uidColumnName)
    {
        $this->uidColumnName = $uidColumnName;
    }
    
    public function getProviderId()
    {
        return $this->providerId;
    }

    public function setProviderId($providerId)
    {
        $this->providerId = $providerId;
    }

    abstract public function getOAuthUrl();
}
