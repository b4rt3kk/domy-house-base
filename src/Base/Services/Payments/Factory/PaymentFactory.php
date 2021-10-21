<?php

namespace Base\Services\Payments\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PaymentFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $payment = new $requestedName();
        
        if (!$payment instanceof \Base\Services\Payments\AbstractPayment) {
            throw new \Exception(sprintf("Klasa %s musi dziedziczyć po %s", $requestedName, \Base\Services\Payments\AbstractPayment::class));
        }
        
        // pobranie konfiguracji dla płatności
        $config = $container->get('ApplicationConfig')['payments'][$payment->getCode()];
        
        //$payment->setServiceManager($container);
        $payment->setConfig($config);
        
        return $payment;
    }
}
