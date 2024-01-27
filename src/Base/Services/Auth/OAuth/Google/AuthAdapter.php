<?php

namespace Base\Services\Auth\OAuth\Google;

class AuthAdapter extends \Base\Services\Auth\OAuth\AbstractOAuth
{
    protected $GOauthApplicationName;
    
    protected $GOauthClientId;
    
    protected $GOauthClientSecret;
    
    protected $GOauthRedirectUri;
    
    public function getGOauthClientId()
    {
        return $this->GOauthClientId;
    }

    public function getGOauthClientSecret()
    {
        return $this->GOauthClientSecret;
    }

    public function setGOauthClientId($GOauthClientId)
    {
        $this->GOauthClientId = $GOauthClientId;
    }

    public function setGOauthClientSecret($GOauthClientSecret)
    {
        $this->GOauthClientSecret = $GOauthClientSecret;
    }
    
    public function getGOauthApplicationName()
    {
        return $this->GOauthApplicationName;
    }

    public function getGOauthRedirectUri()
    {
        return $this->GOauthRedirectUri;
    }

    public function setGOauthApplicationName($GOauthApplicationName)
    {
        $this->GOauthApplicationName = $GOauthApplicationName;
    }

    public function setGOauthRedirectUri($GOauthRedirectUri)
    {
        $this->GOauthRedirectUri = $GOauthRedirectUri;
    }

    public function authenticate()
    {
        diee('auth');
    }
    
    public function register()
    {
        diee('register');
    }
    
    public function getOAuthUrl()
    {
        $gClient = new \Google\Client();
        $gClient->setApplicationName($this->getGOauthApplicationName());
        $gClient->setClientId($this->getGOauthClientId());
        $gClient->setClientSecret($this->getGOauthClientSecret());
        $gClient->setRedirectUri($this->getGOauthRedirectUri());
        $gClient->addScope(\Google\Service\Drive::DRIVE_METADATA_READONLY);
        
        return $gClient->createAuthUrl();
    }
}
