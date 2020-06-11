<?php
namespace Base\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

class AbstractFormFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $return = false;
        
        if (class_exists($requestedName)) {
            $return = in_array(\Laminas\Form\FormInterface::class, class_implements($requestedName));
        }
        
        return $return;
    }
    
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $class = new $requestedName();
        /* @var $class \Base\Form\AbstractForm */
        $class->setServiceManager($container);
        
        return $class;
    }
}
