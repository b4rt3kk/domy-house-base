<?php

namespace Base\Db\Adapter;

class AdapterAbstractServiceFactory extends \Laminas\Db\Adapter\AdapterAbstractServiceFactory
{
    public function __invoke(\Interop\Container\ContainerInterface $container, $requestedName, array $options = null)
    {
        /* Docs odnośnie profilera */
        /* @url https://packagist.org/packages/bjyoungblood/bjy-profiler */
        $config = $this->getConfig($container);
        $dbConfig = $config[$requestedName];
        
        $adapter = new \BjyProfiler\Db\Adapter\ProfilingAdapter($dbConfig);
        $adapter->setProfiler(new \BjyProfiler\Db\Profiler\Profiler());
        /* @todo Zrobienie profilera również dla zapytań uruchamianych przez cli */
        
        if (isset($dbConfig['options']) && is_array($dbConfig['options'])) {
            $options = $dbConfig['options'];
        } else {
            $options = [];
        }
        
        $adapter->injectProfilingStatementPrototype($options);

        return $adapter;
    }
}


