<?php
namespace Base\Mvc\Controller\Plugin\Factory;

use Base\Mvc\Controller\Plugin\AccessPlugin;
use Base\Services\Rbac\RbacManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AccessPluginFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $rbacManager = $container->get(RbacManager::class);
        
        $plugin = new AccessPlugin();
        $plugin->setRbacManager($rbacManager);
        
        return $plugin;
    }
}