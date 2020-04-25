<?php
namespace Base\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AccessFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $rbacManager = $container->get(\Base\Services\Rbac\RbacManager::class);
        
        $helper = new \Base\View\Helper\Access();
        $helper->setRbacManager($rbacManager);
        
        return $helper;
    }
}
