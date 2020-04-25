<?php
namespace Base\Mvc\Controller;

abstract class AbstractActionController extends \Laminas\Mvc\Controller\AbstractActionController
{
    protected $serviceManager;
    
    public function __construct($serviceManager)
    {
        $this->setServiceManager($serviceManager);
    }
    
    
    /**
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setServiceManager(\Laminas\ServiceManager\ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
}
