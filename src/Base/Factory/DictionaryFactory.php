<?php

namespace Base\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DictionaryFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return \Base\Dictionary
     * @throws \Exception
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $dictionary = new $requestedName();
        /* @var $logic \Base\Dictionary */
        
        if (!$dictionary instanceof \Base\Dictionary) {
            throw new \Exception(sprintf("Słownik musi dziedziczyć po %s", \Base\Dictionary::class));
        }
        
        $dictionary->setServiceManager($container);

        return $dictionary;
    }
}
