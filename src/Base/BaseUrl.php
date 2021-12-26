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
    
    public function setSubdomain($subdomain)
    {
        $this->subdomain = $subdomain;
    }
    
    public function getSubdomain()
    {
        $subdomain = $this->subdomain;
        
        if (empty($subdomain)) {
            $url = $this->getUrl();
            $fullUrl = $url;

            preg_match('#^http?[s]://#', $url, $matches);
            $scheme = $matches[0];

            if (!empty($scheme)) {
                $url = str_replace($scheme, '', $url);
            }

            $chunks = explode('.', ltrim($url, 'www.'));
            
            $subdomain = $chunks[0];
        }
        
        return $subdomain;
    }
}
