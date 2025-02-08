<?php

namespace Base\Traits;

trait ServiceManagerTrait 
{
    /**
     * @var \Laminas\ServiceManager\ServiceManager
     */
    protected $serviceManager;
    
    /**
     * Pobierz obiekt ServiceManager
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public function getServiceManager() : \Laminas\ServiceManager\ServiceManager
    {
        $serviceManager = \Base\ServiceManager::getInstance();
        
        $return = $this->serviceManager;
        
        if ($serviceManager instanceof \Laminas\ServiceManager\ServiceManager) {
            $return = $serviceManager;
        }
        
        return $return;
    }

    /**
     * Przypisz obiekt ServiceManager do instancji klasy Singleton
     * @param \Laminas\ServiceManager\ServiceManager|null $serviceManager
     * @return void
     */
    public function setServiceManager($serviceManager) : void
    {
        if (!$this->getServiceManager() instanceof \Laminas\ServiceManager\ServiceManager) {
            \Base\ServiceManager::setInstance($serviceManager);
        }
    }
}
