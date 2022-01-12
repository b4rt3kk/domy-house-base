<?php

namespace Base\Logger;

class Logger extends \Base\Logic\AbstractLogic
{
    const MESSAGE_INFO = 1;
    const MESSAGE_SUCCESS = 2;
    const MESSAGE_WARNING = 3;
    const MESSAGE_ERROR = 4;
    
    protected $drivers = [];
    
    /**
     * @return Driver\AbstractDriver[]
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    public function setDrivers(array $drivers = [])
    {
        $this->drivers = $drivers;
    }
    
    public function addDriver(Driver\AbstractDriver $driver)
    {
        $this->drivers[] = $driver;
    }
    
    public function logMessage($message, $messageType, $additionalData = [])
    {
        
    }
}
