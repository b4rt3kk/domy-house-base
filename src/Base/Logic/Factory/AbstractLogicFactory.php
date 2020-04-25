<?php
namespace Base\Logic\Factory;

use Base\Logic\LogicInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

class AbstractLogicFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $return = false;
        
        if (class_exists($requestedName)) {
            $return = in_array(LogicInterface::class, class_implements($requestedName));
        }
        
        return $return;
    }
    
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $logic = new $requestedName();
        /* @var $logic \Base\Logic\AbstractLogic */
        $logic->setServiceManager($container);
        
        return $logic;
    }
}
