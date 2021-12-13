<?php

namespace Base\Command;

class Command extends \Symfony\Component\Console\Command\Command implements CommandInterface
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
}
