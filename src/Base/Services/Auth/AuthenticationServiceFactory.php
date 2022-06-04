<?php
/**
 * Klasa jest abstrakcyjna, tak by użytkownik miał możliwość konfiguracji niektórych parametrów adaptera,
 * które mogą się różnić w zależności od aplikacji.
 * Przykładowo:
 * - nazwa modelu (warunek konieczny)
 * - nazwy kolumn
 * - warunki wyszukiwania wiersza użytkownika
 */
namespace Base\Services\Auth;

use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Session\SessionManager;
use Laminas\Authentication\Storage\Session as SessionStorage;

abstract class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $sessionManager = $container->get(SessionManager::class);
        /* @var $sessionManager SessionManager */
        
        if (!$sessionManager->isValid()) {
            // jeśli sesja nie jest poprawna (np. zmieniły się parametry przeglądarki)
            $sessionManager->destroy();
        }
        
        $authStorage = new SessionStorage('Laminas_Auth', 'session', $sessionManager);
        $authAdapter = new AuthAdapter();
        /* @var $authAdapter AuthAdapter */
        $authAdapter->setServiceManager($container);
        
        return new AuthenticationService($authStorage, $authAdapter);
    }
}
