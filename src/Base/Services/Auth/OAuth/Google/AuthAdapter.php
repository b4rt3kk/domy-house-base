<?php

namespace Base\Services\Auth\OAuth\Google;

class AuthAdapter extends \Base\Services\Auth\OAuth\AbstractOAuth
{
    protected $GOauthClientId;
    
    protected $GOauthClientSecret;
    
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

    public function authenticate()
    {
        diee('auth');
    }
    
    public function register()
    {
        diee('register');
    }
}
