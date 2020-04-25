<?php
namespace Base\Mvc\Plugin\Factory;

use Interop\Container\ContainerInterface;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FlashMessengerFactory implements FactoryInterface 
{    
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) 
    {
        $flashMessenger = new FlashMessenger();
        
        return $flashMessenger;
    }
    
}