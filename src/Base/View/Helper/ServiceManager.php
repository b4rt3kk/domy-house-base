<?php
namespace Base\View\Helper;

class ServiceManager extends \Laminas\View\Helper\AbstractHelper
{
    protected $serviceManager;
    
    /**
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function get($name)
    {
        $serviceManager = $this->getServiceManager();
        
        return $serviceManager->get($name);
    }
}
