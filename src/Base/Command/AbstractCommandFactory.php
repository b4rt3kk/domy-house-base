<?php

namespace Base\Command;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

class AbstractCommandFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $return = false;

        if (class_exists($requestedName)) {
            $return = in_array(CommandInterface::class, class_implements($requestedName));
        }

        return $return;
    }

    /**
     * @param ContainerInterface $container
     * @param type $requestedName
     * @param array $options
     * @return \Base\Command\Command
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $logic = new $requestedName();
        /* @var $logic \Base\Command\Command */
        $logic->setServiceManager($container);

        return $logic;
    }
}
