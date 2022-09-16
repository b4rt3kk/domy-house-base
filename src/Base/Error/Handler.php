<?php

namespace Base\Error;

class Handler
{   
    protected $logDrivers = [];
    
    public function addLogDriver(\Base\Logger\Driver\AbstractDriver $driver)
    {
        $this->logDrivers[] = $driver;
    }
    
    /**
     * @return \Base\Logger\Driver\AbstractDriver[]
     */
    public function getLogDrivers()
    {
        return $this->logDrivers;
    }

    public function setLogDrivers(array $logDrivers)
    {
        foreach ($logDrivers as $logDriver) {
            $this->addLogDriver($logDriver);
        }
    }
    
    public function handle(int $errorNumber, string $errorString, string $errorFile, int $errorLine, array $errorContext) 
    {
        $drivers = $this->getLogDrivers();
        
        foreach ($drivers as $driver) {
            $message  = intl_error_name($errorNumber) . "\r\n";
            $message .= "File: " . $errorFile . " [line: " . $errorLine . "]" . "\r\n";
            $message .= "Message: " . $errorString . "\r\n";

            $driver->logMessage($message, null);
        }
    }
}
