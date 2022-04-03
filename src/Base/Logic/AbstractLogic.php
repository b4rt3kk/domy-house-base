<?php
namespace Base\Logic;

abstract class AbstractLogic implements LogicInterface
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
    
    public function unserializeJqueryArray($data)
    {
        $return = [];
        
        foreach ($data as $row) {
            $return[$row['name']] = $row['value'];
        }
        
        return $return;
    }
    
    public function generateRandomString($length = 32)
    {
        $return = null;
        $chars = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890';
        
        for ($i = 0; $i < $length; $i++) {
            $return .= $chars[mt_rand(0, strlen($chars) -1)];
        }
        
        return $return;
    }
    
    public function getAsUrlName($name, $separator = '-')
    {
        $charsToReplace = 'ęóąśłżźćń';
        $charsReplacements = 'eoaslzzcn';
        
        $return = mb_strtolower(preg_replace("#\s#", $separator, trim($name)), 'UTF-8');
        
        // zamiana polskich znaków
        $return = str_replace(mb_str_split($charsToReplace, 1, 'UTF-8'), str_split($charsReplacements), $return);
        
        return $return;
    }
}
