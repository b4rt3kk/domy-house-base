<?php
namespace Base\Services\Auth;

use Base\Logic\AbstractLogic;

class AuthManager extends AbstractLogic
{
    const ACCESS_GRANTED = 'access_granted';
    const ACCESS_DENIED = 'access_denied';
    const AUTH_REQUIRED = 'auth_required';
    
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
    
    public function register($data, $forceAdd = false)
    {
        $serviceManager = $this->getServiceManager();
        
        if ($data instanceof \Base\Form\AbstractForm) {
            $data = $data->getData();
        }
        
        $authenticationService = $serviceManager->get(\Laminas\Authentication\AuthenticationService::class);
        /* @var $authenticationService \Laminas\Authentication\AuthenticationService */
        $adapter = $authenticationService->getAdapter();
        /* @var $adapter \Base\Services\Auth\AuthAdapter */

        if ($authenticationService->getIdentity() !== null && !$forceAdd) {
            throw new \Exception('You are already logged');
        }

        $adapter->setLogin($data[$adapter->getLoginColumnName()]);
        $adapter->setPassword($data[$adapter->getPasswordColumnName()]);
        $adapter->setAdditionalData($data);
        
        $adapter->register();
    }
    
    /**
     * Metoda sprawdza czy użytkownik ma dostęp do wskazanego route i akcji
     * @param string $routeName
     * @param string $actionName
     * @return string
     */
    public function filterAccess($routeName, $actionName)
    {
        $return = self::ACCESS_DENIED;
        $auth = $this->getServiceManager()->get(\Laminas\Authentication\AuthenticationService::class);
        /* @var $auth \Laminas\Authentication\AuthenticationService */
        $identity = $auth->getIdentity();
        
        $rbacManager = $this->getServiceManager()->get(\Base\Services\Rbac\RbacManager::class);
        /* @var $rbacManager \Base\Services\Rbac\RbacManager */
        $permission = $routeName . '.' . $actionName;
        
        $isGranted = $rbacManager->isGranted($identity, $permission);
        
        if ($isGranted) {
            $return = self::ACCESS_GRANTED;
        } else if (empty($identity)) {
            $return = self::AUTH_REQUIRED;
        }
        
        return $return;
    }
}
