<?php

namespace Base\Services\Auth\OAuth;

abstract class AbstractOAuth extends \Base\Services\Auth\AbstractAuthAdapter
{
    protected $providerColumnName = 'id_oauth_provider';
    
    protected $uidColumnName = 'oauth_uid';
    
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
}
