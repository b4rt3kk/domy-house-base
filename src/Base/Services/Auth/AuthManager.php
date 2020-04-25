<?php
namespace Base\Services\Auth;

use Base\Logic\AbstractLogic;

class AuthManager extends AbstractLogic
{
    public function login($data)
    {
        $serviceManager = $this->getServiceManager();
        
        if ($data instanceof \Base\Form\AbstractForm) {
            $data = $data->getData();
        }
        
        $authenticationService = $serviceManager->get(\Laminas\Authentication\AuthenticationService::class);
        /* @var $authenticationService \Laminas\Authentication\AuthenticationService */
        $adapter = $authenticationService->getAdapter();
        /* @var $adapter \Base\Services\Auth\AuthAdapter */
        
        if ($authenticationService->getIdentity() !== null) {
            throw new \Exception('You are already logged');
        }
        
        $adapter->setLogin($data[$adapter->getLoginColumnName()]);
        $adapter->setPassword($data[$adapter->getPasswordColumnName()]);
        
        $result = $authenticationService->authenticate();
        
        if ($result->getCode() !== \Laminas\Authentication\Result::SUCCESS) {
            throw new \Exception(implode(', ', $result->getMessages()));
        }
    }
    
    public function logout()
    {
        $serviceManager = $this->getServiceManager();
        $authenticationService = $serviceManager->get(\Laminas\Authentication\AuthenticationService::class);
        /* @var $authenticationService \Laminas\Authentication\AuthenticationService */
        $adapter = $authenticationService->getAdapter();
        /* @var $adapter \Base\Services\Auth\AuthAdapter */
        
        if ($authenticationService->getIdentity() === null) {
            throw new \Exception('You are not logged in');
        }
        
        $authenticationService->clearIdentity();
        $adapter->callEvent(AuthAdapter::EVENT_LOGOUT);
    }
    
    public function createPassword($password)
    {
        $serviceManager = $this->getServiceManager();
        $authenticationService = $serviceManager->get(\Laminas\Authentication\AuthenticationService::class);
        /* @var $authenticationService \Laminas\Authentication\AuthenticationService */
        $adapter = $authenticationService->getAdapter();
        /* @var $adapter \Base\Services\Auth\AuthAdapter */
        
        $crypt = $adapter->getCrypt();
        
        $passwordEncrypted = $crypt->create($password);
        
        return $passwordEncrypted;
    }
    
    public function register($data)
    {
        $serviceManager = $this->getServiceManager();

        if ($data instanceof \Base\Form\AbstractForm) {
            $data = $data->getData();
        }

        $authenticationService = $serviceManager->get(\Laminas\Authentication\AuthenticationService::class);
        /* @var $authenticationService \Laminas\Authentication\AuthenticationService */
        $adapter = $authenticationService->getAdapter();
        /* @var $adapter \Base\Services\Auth\AuthAdapter */

        if ($authenticationService->getIdentity() !== null) {
            throw new \Exception('You are already logged');
        }

        $adapter->setLogin($data[$adapter->getLoginColumnName()]);
        $adapter->setPassword($data[$adapter->getPasswordColumnName()]);
        $adapter->setAdditionalData($data);
        
        $adapter->register();
    }
}
