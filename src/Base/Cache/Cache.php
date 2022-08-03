<?php
namespace Base\Cache;


class Cache extends \Base\Logic\AbstractLogic
{
    protected $cachePrefix;
    
    public function getCachePrefix()
    {
        return $this->cachePrefix;
    }

    public function setCachePrefix($cachePrefix): void
    {
        $this->cachePrefix = $cachePrefix;
    }
    
    public function setItem($name, $value)
    {
        $storage = $this->getStorage();
        
        $storage->addItem($name, $value);
    }
    
    public function getItem($name)
    {
        $storage = $this->getStorage();
        
        return $storage->getItem($name);
    }
    
    /**
     * Pobierz adapter cache
     * @return \Laminas\Cache\Storage\Adapter\AbstractAdapter
     */
    protected function getStorage()
    {
        $storageFactory = $this->getServiceManager()->get(\Laminas\Cache\Service\StorageAdapterFactoryInterface::class);
        /* @var $storageFactory \Laminas\Cache\Service\StorageAdapterFactory */

        $config = $this->getServiceManager()->get('Config')['cache'];

        $cache = $storageFactory->createFromArrayConfiguration($config);

        return $cache;
    }
}
