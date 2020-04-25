<?php
namespace Base\Mvc\Controller\Plugin;

class AccessPlugin extends \Laminas\Mvc\Controller\Plugin\AbstractPlugin
{
    protected $rbacManager;
    
    public function __invoke($permission, $params = [])
    {
        $rbac = $this->getRbacManager();
        
        return $rbac->isGranted(null, $permission, $params);
    }
    
    /**
     * @return \Base\Services\Rbac\RbacManager
     */
    public function getRbacManager()
    {
        return $this->rbacManager;
    }

    public function setRbacManager($rbacManager)
    {
        $this->rbacManager = $rbacManager;
    }
}
