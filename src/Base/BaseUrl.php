<?php

namespace Base;

class BaseUrl extends \Base\Logic\AbstractLogic
{
    protected static $instance;
    
    protected $subdomain;
    
    /**
     * @return \Base\BaseUrl
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof BaseUrl) {
            self::$instance = new BaseUrl();
        }

        return self::$instance;
    }

    public function getUrl()
    {
        $serverUrl = $this->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
        $baseUrl = $serverUrl->__invoke();
        
        return $baseUrl;
    }
    
    /**
     * Pobierz scheme dla obecnie otwartej strony (http:// lub https://)
     * @return string
     */
    public function getScheme()
    {
        $baseUrl = $this->getUrl();
        $matches = [];
        
        
        preg_match('#^http?[s]://#', $baseUrl, $matches);
        $scheme = $matches[0];
        
        return $scheme;
    }
    
    /**
     * Pobierz nazwę hosta (bez scheme)
     * @return string
     */
    public function getHostName()
    {
        $url = $this->getUrl();
        $scheme = $this->getScheme();
        
        return str_replace($scheme, '', $url);
    }
    
    /**
     * Pobierz nazwę hosta z wyłączeniem subdomeny, o ile jest to możliwe do określenia,
     * tzn. jeśli nazwa hosta została podana w konfiguracji aplikacji
     * @return string
     */
    public function getBaseHostName()
    {
        $config = $this->getServiceManager()->get('ApplicationConfig');
        $hostName = $config['host_name'];
        
        if (empty($hostName)) {
            // nie skonfigurowano nazwy hosta, należy go określić na podstawie url
            // niestety w ten sposób nie zostanie wycięta subdomena
            $hostName = $this->getHostName();
        }
        
        return $hostName;
    }
    
    public function setSubdomain($subdomain)
    {
        $this->subdomain = $subdomain;
    }
    
    /**
     * Określ nazwę subdomeny lub jeśli nie jest to możliwe to zwróć null
     * @return string|null
     */
    public function getSubdomain()
    {
        $subdomain = $this->subdomain;
        
        if (empty($subdomain)) {
            $config = $this->getServiceManager()->get('ApplicationConfig');
            
            $hostName = $this->getHostName();
            $baseHostName = $config['host_name'];
            
            if (!empty($baseHostName) && 1 ==2) {
                // określono bazową nazwę hosta w konfiguracji
                // nazwa subomeny jest różnicą pomiędzy pobraną nazwą hosta, a nazwą hosta podaną w konfiguracji
                $subdomain = trim(str_replace([$baseHostName, 'www.'], '', $hostName), '.');
            } else {
                $chunks = explode('.', ltrim($hostName, 'www.'));
                
                if (sizeof($chunks) > 2) {
                    $subdomain = $chunks[0];
                }
            }
        }
        
        return $subdomain;
    }
}
