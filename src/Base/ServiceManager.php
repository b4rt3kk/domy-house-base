<?php

namespace Base;

class ServiceManager
{
    protected static $instance;
    
    /**
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public static function getInstance()
    {
        return self::$instance;
    }
    
    public static function setInstance(\Laminas\ServiceManager\ServiceManager $serviceManager)
    {
        self::$instance = $serviceManager;
    }
}
