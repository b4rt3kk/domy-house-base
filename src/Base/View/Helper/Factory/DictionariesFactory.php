<?php
namespace Base\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DictionariesFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $helper = new \Base\View\Helper\Dictionaries();
        $helper->setServiceManager($container);
        
        return $helper;
    }
}
