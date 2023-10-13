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
        $serviceManager = \Base\ServiceManager::getInstance();
        
        $return = $this->serviceManager;
        
        if ($serviceManager instanceof \Laminas\ServiceManager\ServiceManager) {
            $return = $serviceManager;
        }
        
        return $return;
    }

    public function setServiceManager($serviceManager)
    {
        if (!$this->getServiceManager() instanceof \Laminas\ServiceManager\ServiceManager) {
            \Base\ServiceManager::setInstance($serviceManager);
        }
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
        
        // zamiania wszystkich białych znaków na separator
        $return = mb_strtolower(preg_replace("#\s+#", $separator, trim($name)), 'UTF-8');
        
        // zamiana polskich znaków
        $return = str_replace(mb_str_split($charsToReplace, 1, 'UTF-8'), str_split($charsReplacements), $return);
        
        // wyrzucenie wszystkich niedozwolonych znaków
        $return = preg_replace(sprintf("#[^A-Za-z0-9\%s]#", $separator), '', $return);
        
        return $return;
    }
}
