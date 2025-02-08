<?php
namespace Base\Mvc\Controller;

abstract class AbstractActionController extends \Laminas\Mvc\Controller\AbstractActionController
{
    use \Base\Traits\ServiceManagerTrait;
    
    public function __construct($serviceManager)
    {
        $this->setServiceManager($serviceManager);
    }
    
    public function onDispatch(\Laminas\Mvc\MvcEvent $e)
    {
        $application = $e->getApplication();
        $serviceManager = $application->getServiceManager();
        
        $controller = $e->getTarget();
        $routeName = $e->getRouteMatch()->getMatchedRouteName();
        $actionName = $e->getRouteMatch()->getParam('action', null);
        
        $authManager = $serviceManager->get(\Base\Services\Auth\AuthManager::class);
        /* @var $authManager \Base\Services\Auth\AuthManager */
        
        // sprawdzenie czy użytkownik ma dostęp do zasobu
        $result = $authManager->filterAccess($routeName, $actionName);
        
        switch ($result) {
            case \Base\Services\Auth\AuthManager::ACCESS_DENIED:
                if ($routeName !== 'auth' || $actionName !== 'notauthorized') {
                    return $controller->redirect()->toRoute('auth', ['action' => 'notauthorized']);
                }
                break;
            case \Base\Services\Auth\AuthManager::AUTH_REQUIRED:
                if ($routeName !== 'auth' || $actionName !== 'login') {
                    $controller->flashMessenger()->addErrorMessage('Musisz się zalogować by uzyskać dostęp do tego zasobu');
                    
                    if ($routeName !== 'ajax') {
                        $authManager->setRedirectRoute($e->getRouteMatch());
                    }
                    
                    return $controller->redirect()->toRoute('auth', ['action' => 'login']);
                }
                break;
        }

        return parent::onDispatch($e);
    }
}
