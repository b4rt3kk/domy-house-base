<?php

namespace Base\Services\Payments;

abstract class AbstractPayment
{
    protected $serviceManager;
    
    protected $code;
    
    protected $config = [];
    
    protected $params = [];
    
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
    
    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }
    
    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig($config)
    {
        foreach ($config as $name => $value) {
            $this->setConfigValue($name, $value);
        }
    }
    
    public function setConfigValue($name, $value)
    {
        $normalizedName = $this->getNormalizedName($name);
        $methodName = 'set' . $normalizedName;
        
        if (method_exists($this, $methodName)) {
            $this->{$methodName}($value);
        }
        
        $this->config[$name] = $value;
    }
    
    public function getConfigValue($name, $default = null)
    {
        $data = $this->getConfig();
        
        return array_key_exists($name, $data) ? $data[$name] : $default;
    }
    
    public function getParams()
    {
        return $this->params;
    }
    
    public function setParams($params)
    {
        $this->params = $params;
    }
    
    public function getParamsValues()
    {
        $return = [];
        $params = $this->getParams();
        
        foreach ($params as $param) {
            $normalizedName = $this->getNormalizedName($param);
            $methodName = 'get' . $normalizedName;
            
            if (method_exists($this, $methodName)) {
                $return[$param] = $this->{$methodName}();
            }
        }
        
        return $return;
    }
    
    protected function getPropertiesNames()
    {
        $return = [];
        $reflectionClass = new \ReflectionClass($this);
        
        $properties = $reflectionClass->getProperties();
        
        foreach ($properties as $property) {
            /* @var $property \ReflectionProperty */
            $return[] = $property->getName();
        }
        
        return $return;
    }
    
    protected function getNormalizedName($name)
    {
        $chunks = explode('_', $name);
        
        $return = implode('', array_map('ucfirst', $chunks));
        
        return $return;
    }
    
    protected function getUnnormalizedName($name)
    {
        $chunks = preg_split('/(?=[A-Z])/',$name);
        
        $return = implode('_', array_map('uclower', $chunks));
        
        return $return;
    }
}
