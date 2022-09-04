<?php
namespace Base\Config;

class Config 
{
    protected static $instance;
    
    protected $data = [];

    /**
     * @return \Base\Config\Config
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof Config) {
            self::$instance = new Config();
        }

        return self::$instance;
    }
    
    public function setVariable($name, $value)
    {
        $this->data[$name] = $value;
    }
    
    public function getVariable($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }
}
