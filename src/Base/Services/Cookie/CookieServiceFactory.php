<?php

namespace Base\Services\Cookie;

class CookieServiceFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{
    /**
     * @param \Interop\Container\ContainerInterface $container
     * @param type $requestedName
     * @param array $options
     * @return \Base\Services\Cookie\Cookie
     */
    public function __invoke(\Interop\Container\ContainerInterface $container, $requestedName, array $options = null)
    {
        $cookie = new Cookie();
        $cookie->setServiceManager($container);
        $cookie->setHashService(new \Base\Services\Hash\Md5());
        
        return $cookie;
    }
}
