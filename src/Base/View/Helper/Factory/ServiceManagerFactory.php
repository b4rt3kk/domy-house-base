<?php
namespace Base\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ServiceManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceManager = new \Base\View\Helper\ServiceManager();
        $serviceManager->setServiceManager($container);
        
        return $serviceManager;
    }
}
