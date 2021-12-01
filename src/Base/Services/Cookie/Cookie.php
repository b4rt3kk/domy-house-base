<?php

namespace Base\Services\Cookie;

class Cookie extends \Base\Logic\AbstractLogic
{   
    protected $hashService;
 
    /**
     * @return \Base\Services\Hash\AbstractHash
     */
    public function getHashService()
    {
        return $this->hashService;
    }

    public function setHashService($hashService)
    {
        $this->hashService = $hashService;
    }
    
    /**
     * Wygeneruj klucz identyfikacyjny użytkownika do zapisania w cookie
     * @return string
     * @throws \Exception
     */
    public function generateCookieIdentityKey()
    {
        $hashService = $this->getHashService();
        
        if (!$hashService instanceof \Base\Services\Hash\AbstractHash) {
            throw new \Exception(sprintf("Klasa hashująca musi dziedziczyć po %s", \Base\Services\Hash\AbstractHash::class));
        }
        
        return $hashService->getHash($this->getCleanValidationString());
    }
    
    /**
     * Sprawdź czy podany w parametrze klucz walidacyjny jest zgodny z tym wygenerowanym na podstawie danych m.in. przeglądarki czy IP
     * @param string $key
     * @return boolean
     * @throws \Exception
     */
    public function validateCookieIdentityKey($key)
    {
        $hashService = $this->getHashService();
        
        if (!$hashService instanceof \Base\Services\Hash\AbstractHash) {
            throw new \Exception(sprintf("Klasa hashująca musi dziedziczyć po %s", \Base\Services\Hash\AbstractHash::class));
        }
        
        $validationString = $this->getCleanValidationString();
        $hashedValidationString = $hashService->getHash($validationString);
        
        return $hashedValidationString === $key;
    }
    
    protected function getCleanValidationString()
    {
        $request = $this->getServiceManager()->get('Request');
        $remoteAddress = new \Laminas\Http\PhpEnvironment\RemoteAddress();
        
        // dane przeglądarki użytkownika
        $userAgent = $request->getServer('HTTP_USER_AGENT');
        // adres IP
        $ipAddress = $remoteAddress->getIpAddress();
        
        return $userAgent . $ipAddress;
    }
}
