<?php
/**
 * Klasa jest abstrakcyjna, tak by użytkownik miał możliwość konfiguracji niektórych parametrów klasy,
 * które mogą się różnić w zależności od aplikacji.
 * Przykładowo:
 * - sposób transportu, np. Sendmail (warunek konieczny)
 */
namespace Base\Services\Transport\Factory;

use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\Factory\FactoryInterface;

abstract class MailFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return \Base\Services\Transport\Mail
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $mail = new \Base\Services\Transport\Mail();
        $mail->setServiceManager($container);
        
        return $mail;
    }
}

