<?php
namespace Base\Services\Rbac;

use Laminas\Permissions\Rbac\Rbac;

class RbacAssertionManager extends AbstractRbacAssertionManager
{ 
    public function assert(Rbac $rbac, $permission, $params = [])
    {
        $permissionParams = $this->getPermissionParams($permission);
        $authenticationService = $this->getServiceManager()->get(\Laminas\Authentication\AuthenticationService::class);
        /* @var $authenticationService \Laminas\Authentication\AuthenticationService */
        $identity = $authenticationService->getIdentity();
        
        $isGranted = false;
        
        switch ($permissionParams['mode']) {
            case self::MODE_ALL:
                $isGranted = true;
                break;
            case self::MODE_OWN:
                if (!empty($identity)) {
                    if ($params[$this->getCreatorIdName()] == $identity->id) {
                        $isGranted = true;
                    }
                }
                break;
            default:
                $callable = $this->getCallable($permissionParams['mode']);
                
                if (is_callable($callable)) {
                    $isGranted = $callable($this, $rbac, $permission, $params);
                }
                break;
        }
        
        return $isGranted;
    }
}
