<?php

namespace Base\Services\Auth\OAuth\Google;

class AuthAdapter extends \Base\Services\Auth\OAuth\AbstractOAuth
{
    protected $GOauthApplicationName;
    
    protected $GOauthClientId;
    
    protected $GOauthClientSecret;
    
    protected $GOauthRedirectUri;
    
    protected $uid;
    
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
    
    public function getUid()
    {
        return $this->uid;
    }

    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    public function authenticate()
    {
        $code = $this->getCode();
        
        $client = $this->getClient();
        $storageContainer = $this->getStorageContainer();
        
        if (!empty($code)) {
            $client->fetchAccessTokenWithAuthCode($code);
            
            // pobranie tokena i zapisanie w sesji
            $token = $client->getAccessToken($client);
            $storageContainer->token = $token;
        }
        
        if (isset($storageContainer->token)) {
            $token = $storageContainer->token;
            
            // ustanowienie tokena zapisanego w sesji
            $client->setAccessToken($token);
        }
        
        if ($client->getAccessToken()) {
            $service = $this->getService($client);
            
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
                // przypisanie pobranych danych
                $this->setLogin($userInfo->email);
                $this->setUid($userInfo->id);
                // rejestracja nowego użytkownika
                return $this->register();
            }
        }
    }
    
    public function register()
    {
        $loginColumn = $this->getLoginColumnName();
        
        $login = $this->getLogin();
        
        if (empty($login)) {
            throw new \Exception('Login cannot be empty');
        }
        
        $rowUser = $this->getUserByLoginRow($login);
        
        if (!empty($rowUser)) {
            throw new \Exception('User already exists');
        }
        
        $remoteAddress = new \Laminas\Http\PhpEnvironment\RemoteAddress();
        
        $model = $this->getModel();
        $table = $model->getEntity();
        
        $data = array_merge($this->getAdditionalData(), [
            $loginColumn => $login,
            $this->getProviderColumnName() => $this->getProviderId(),
            $this->getUidColumnName() => $this->getUid(),
            'register_ip_address' => $remoteAddress->getIpAddress(),
        ]);
        
        $table->exchangeArray($data);
        
        $adapter = $this->getServiceManager()->get('main');
        /* @var $adapter \Laminas\Db\Adapter\Adapter */

        $adapter->getDriver()->getConnection()->beginTransaction();
        
        try {
            $id = $model->createRow($table);

            $row = $this->getUserByIdRow($id);

            $this->setUserRow($row);

            $this->callEvent(self::EVENT_REGISTER_SUCCESS);
            
            $adapter->getDriver()->getConnection()->commit();
            
            $result = new \Laminas\Authentication\Result(\Laminas\Authentication\Result::SUCCESS, $row, ['Authenticated successfully']);
            
            $this->callEvent(self::EVENT_LOGIN_SUCCESS);

            return $result;
        } catch (\Exception $e) {
            $adapter->getDriver()->getConnection()->rollback();
            throw $e;
        }
    }
    
    public function logout()
    {
        $client = $this->getClient();
        $client->revokeToken();
        
        $this->clearStorageCondainerData();
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
