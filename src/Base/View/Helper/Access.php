<?php
namespace Base\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class Access extends AbstractHelper
{
    protected $rbacManager;
    
    /**
     * 
     * @return \Base\Services\Rbac\RbacManager
     */
    public function getRbacManager()
    {
        return $this->rbacManager;
    }

    public function setRbacManager(\Base\Services\Rbac\RbacManager $rbacManager)
    {
        $this->rbacManager = $rbacManager;
    }

    public function __invoke($permission, $params = [])
    {
        $rbacManager = $this->getRbacManager();
        
        return $rbacManager->isGranted(null, $permission, $params);
    }
}
