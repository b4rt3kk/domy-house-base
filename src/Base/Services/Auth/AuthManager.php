<?php
namespace Base\Services\Auth;

use Base\Logic\AbstractLogic;

class AuthManager extends AbstractLogic
{
    const ACCESS_GRANTED = 'access_granted';
    const ACCESS_DENIED = 'access_denied';
    const AUTH_REQUIRED = 'auth_required';
    
    const DEFAULT_VIRTUAL_USER = 'System';
    
    const OUATH_GOOGLE = 'google';
    
    public static $OAuthAdapters = [
        self::OUATH_GOOGLE => \Base\Services\Auth\OAuth\Google\AuthAdapter::class,
    ];
    
    /**
     * @param string $name
     * @return \Base\Services\Auth\OAuth\AbstractOAuth
     * @throws \Exception
     */
    public function getOAuthAdapter($name)
    {
        $serviceManager = $this->getServiceManager();
        
        $authenticationService = $serviceManager->get(\Laminas\Authentication\AuthenticationService::class);
        /* @var $authenticationService \Laminas\Authentication\AuthenticationService */
        $defaultAdapter = $authenticationService->getAdapter();
        /* @var $defaultAdapter \Base\Services\Auth\AbstractAuthAdapter */
        
        if (!in_array($name, array_keys(self::$OAuthAdapters))) {
            throw new \Exception(sprintf('Adapter o nazwie %s nie jest obsługiwany'));
        }
        
        $className = self::$OAuthAdapters[$name];
        
        if (!class_exists($className)) {
            throw new \Exception(sprintf('Klasa o nazwie %s nie istnieje', $className));
        }
        
        $adapter = new $className();
        /* @var $adapter \Base\Services\Auth\OAuth\AbstractOAuth */
        $adapter->setPropertiesValues($defaultAdapter->getPropertiesValues());
        
        return $adapter;
    }
    
    /**
     * Pobierz route dla przekierowania po zalogowaniu
     * @return \Laminas\Router\Http\RouteMatch|null
     */
    public function getRedirectRoute()
    {
        $container = $this->getStorageContainer();
        
        return $container->redirectRoute;
    }

    public function setRedirectRoute($redirectRoute)
    {
        $container = $this->getStorageContainer();

        $container->redirectRoute = $redirectRoute;
    }
    
    public function clearRedirectRoute()
    {
        $container = $this->getStorageContainer();
        
        $container->redirectRoute = null;
    }
    
    public function login($data = [])
    {
        $serviceManager = $this->getServiceManager();
        
        if ($data instanceof \Base\Form\AbstractForm) {
            $data = $data->getData();
        }
        
        $authenticationService = $serviceManager->get(\Laminas\Authentication\AuthenticationService::class);
        /* @var $authenticationService \Laminas\Authentication\AuthenticationService */
        $adapter = $authenticationService->getAdapter();
        /* @var $adapter \Base\Services\Auth\AbstractAuthAdapter */
        
        if ($authenticationService->getIdentity() !== null) {
            throw new \Exception('You are already logged');
        }
        
        if (array_key_exists($adapter->getLoginColumnName(), $data)) {
            $adapter->setLogin($data[$adapter->getLoginColumnName()]);
        }
        
        if (array_key_exists($adapter->getPasswordColumnName(), $data)) {
            $adapter->setPassword($data[$adapter->getPasswordColumnName()]);
        }
        
        if ($_SERVER['REMOTE_ADDR'] == '46.205.208.252') {
            //diee($authenticationService);
            //$oAuth = new OAuth\Google\AuthAdapter();
            //$oAuth->setPropertiesValues($adapter->getPropertiesValues());
            
            //diee($oAuth);
        }
        
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
    
    /**
     * Pobierz obiekt sesji przechowujący dane managera autoryzacji
     * @return \Laminas\Session\Container
     */
    protected function getStorageContainer()
    {
        $container = new \Laminas\Session\Container(get_class($this));

        return $container;
    }
}
