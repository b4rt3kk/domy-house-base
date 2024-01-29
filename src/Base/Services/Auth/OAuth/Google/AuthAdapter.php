<?php

namespace Base\Services\Auth\OAuth\Google;

class AuthAdapter extends \Base\Services\Auth\OAuth\AbstractOAuth
{
    protected $GOauthApplicationName;
    
    protected $GOauthClientId;
    
    protected $GOauthClientSecret;
    
    protected $GOauthRedirectUri;
    
    protected $code;
    
    protected $scope;
    
    protected $token;
    
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
    
    public function getCode()
    {
        return $this->code;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function setScope($scope)
    {
        $this->scope = $scope;
    }
    
    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function authenticate()
    {
        $code = $this->getCode();
        
        $client = $this->getClient();
        $storageContainer = $this->getStorageContainer();
        
        if (!empty($code)) {
            //$client->fetchAccessTokenWithAuthCode($code);
            
            // pobranie tokena i zapisanie w sesji
            //$token = $client->getAccessToken($client);
            //$storageContainer->token = $token;
        }
        
        if (isset($storageContainer->token)) {
            $token = $storageContainer->token;
            
            // ustanowienie tokena zapisanego w sesji
            $client->setAccessToken($token);
        }
        
        if (!empty($code)) {
            $client->fetchAccessTokenWithAuthCode($code);
            
            $token = $client->getAccessToken($client);
            $service = $this->getService($client);
            
            $storageContainer->token = $token;
            
            $userInfo = $service->userinfo->get();
            
            if (empty($userInfo->email)) {
                throw new \Exception("Nie udało się pobrać adresu email");
            }
            
            if (empty($userInfo->id)) {
                throw new \Exception("Nie udało się pobrać id");
            }
            
            $rowUser = $this->getUserByLoginRow($userInfo->email);
            
            $model = $this->getModel();
            $table = $model->getEntity();

            if (!empty($rowUser)) {
                $this->setUserRow($rowUser);
                
                // użytkownik o tym adresie email już istnieje
                $data = [
                    $this->getProviderColumnName() => $this->getProviderId(),
                    $this->getUidColumnName() => $userInfo->id,
                ];
                
                $model->update($data, ['id' => $rowUser->id]);
                
                $result = new \Laminas\Authentication\Result(\Laminas\Authentication\Result::SUCCESS, $rowUser, ['Authenticated successfully']);
                $this->callEvent(self::EVENT_LOGIN_SUCCESS);

                return $result;
            } else {
                // użytkownik nie istnieje
                // rejestracja nowego użytkownika
            }
        }
    }
    
    public function register()
    {
        diee('register');
    }
    
    public function getOAuthUrl()
    {
        $gClient = $this->getClient();
        
        return $gClient->createAuthUrl();
    }
    
    /**
     * @return \Google\Client
     */
    protected function getClient()
    {
        $gClient = new \Google\Client();
        $gClient->setApplicationName($this->getGOauthApplicationName());
        $gClient->setClientId($this->getGOauthClientId());
        $gClient->setClientSecret($this->getGOauthClientSecret());
        $gClient->setRedirectUri($this->getGOauthRedirectUri());
        $gClient->addScope(\Google\Service\Oauth2::USERINFO_EMAIL);
        $gClient->addScope(\Google\Service\Oauth2::USERINFO_PROFILE);
        
        return $gClient;
    }
    
    /**
     * @param object $client
     * @return \Google\Service\Oauth2
     */
    protected function getService($client = null)
    {
        if (empty($client)) {
            $client = $this->getClient();
        }
        
        $service = new \Google\Service\Oauth2($client);
        
        return $service;
    }
}
