<?php

namespace Base\Db\Adapter;

class AdapterAbstractServiceFactory extends \Laminas\Db\Adapter\AdapterAbstractServiceFactory
{
    public function __invoke(\Interop\Container\ContainerInterface $container, $requestedName, array $options = null)
    {
        return parent::__invoke($container, $requestedName, $options);
    }
}


