<?php

namespace Base;

class Debug extends \Base\Logic\AbstractLogic
{
    protected static $instance;
    
    protected $ipWhitelist = [];
    
    /**
     * @return \Base\Debug
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof Debug) {
            self::$instance = new Debug();
        }

        return self::$instance;
    }
    
    public function getIpWhitelist()
    {
        return $this->ipWhitelist;
    }

    public function setIpWhitelist($ipWhitelist): void
    {
        $this->ipWhitelist = $ipWhitelist;
    }
    
    /**
     * @param string $ipAddress
     * @return \Base\Debug
     */
    public function addIpToWhitelist($ipAddress)
    {
        $this->ipWhitelist[] = $ipAddress;
        
        return self::$instance;
    }
    
    public function diee()
    {
        if ($this->isIpAllowed()) {
            diee(func_get_args());
        }
    }
    
    public function dumpp()
    {
        if ($this->isIpAllowed()) {
            dumpp(func_get_args());
        }
    }
    
    public function isIpAllowed()
    {
        $ipWhitelist = $this->getIpWhitelist();
        
        $remoteAddress = new \Laminas\Http\PhpEnvironment\RemoteAddress();
        $ipAddress = $remoteAddress->getIpAddress();
        
        return in_array($ipAddress, $ipWhitelist);
    }
}
