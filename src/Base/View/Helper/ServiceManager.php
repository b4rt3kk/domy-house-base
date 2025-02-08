<?php
namespace Base\View\Helper;

class ServiceManager extends \Laminas\View\Helper\AbstractHelper
{
    use \Base\Traits\ServiceManagerTrait;
    
    public function get($name)
    {
        $serviceManager = $this->getServiceManager();
        
        return $serviceManager->get($name);
    }
}
