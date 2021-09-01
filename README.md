# Base.laminas

Podstawowe klasy i metody dla frameworka Laminas (dawniej Zend Framework 3).
Dzięki niemu można szybciej wprowadzić niektóre funkcjonalności, takie jak np. sprawdzanie uprawnień czy logowanie z rejestracją.

## Instalacja

Najpierw należy dodać nowe repozytorium do pliku `composer.json`:

```
"repositories": [
    {
        "type": "package",
        "package": {
            "name": "bw/base",
            "version": "master",
            "source": {
                "url": "ssh://git@dev-bwiechnik.dyndns.org:64998/BW/Base.laminas.git",
                "type": "git",
                "reference": "master"
            },
            "autoload": {
                "psr-0": {
                    "Base": "src"
                }
            }
        }
    }
]
```

Można to zrobić również przez inny typ - VCS (zalecane):

```
    "repositories": [
        {
            "type": "vcs",
            "url": "ssh://git@dev-bwiechnik.dyndns.org:64998/BW/Base.laminas.git",
            "canonical": false,
            "package": {
                "name": "bw/base",
                "version": "1.3",
                "source": {
                    "url": "ssh://git@dev-bwiechnik.dyndns.org:64998/BW/Base.laminas.git",
                    "type": "git",
                    "reference": "master"
                },
                "autoload": {
                    "psr-0": {
                        "Base": "src"
                    }
                }
            }
        }
    ],
```

Teraz należy wywołać komendę do instalacji paczki:

```
composer require --dev bw/base:master
```

lub w przypadku zalecanej metody wystarczy:

```
composer require bw/base
```


Jeśli jest to konieczne (ponieważ composer wyrzuca błąd):

> Could not find a version of package bw/base matching your minimum-stability (stable). Require it with an explicit version constraint allowing its desired stability.

To należy dodać do pliku `composer.json` następującą linię:

```
"minimum-stability": "dev"
```

## Inicjalizacja potrzebnych elementów w modułach frameworka

### Pliki partiali (widoków)

By można było korzystać z partiali oferowanych przez paczkę `Base` w pliku konfiguracyjnym modułu `module.config.php` należy dodać:

```
'view_manager' => [
    'template_map' => [
        'base/form' => __DIR__ . '/../../../vendor/bw/base/src/view/partials/form.phtml',
        'base/flash_messenger' => __DIR__ . '/../../../vendor/bw/base/src/view/partials/flash_messenger.phtml',
    ],
],
```

Wszystkie partiale z `Base` korzystają z tej konwencji nazewnictwa, więc bez ich dodania do `template_map` mogą nie działać prawidłowo.

### Abstrakcyjne fabryki

Abstrakcyjne fabryki są potrzebne do inicjalizacji np. formularzy. W pliku konfiguracji modułu `module.config.php` należy dodać:

```
'service_manager' => [
    'abstract_factories' => [
        \Base\Form\AbstractFormFactory::class,
        \Base\Logic\Factory\AbstractLogicFactory::class,
    ],
]
```

### Dodawanie nowych pluginów widoku

By dodać plugin do widoku należy edytować plik `Module.php` i utworzyć metodę (jeśli nie istnieje) jak poniżej:

```
public function getViewHelperConfig()
{
    return [
        'aliases' => [
            'serviceManager' => \Base\View\Helper\ServiceManager::class,
        ],
        'factories' => [
            \Base\View\Helper\ServiceManager::class => \Base\View\Helper\Factory\ServiceManagerFactory::class,
        ],
    ];
}
```

Teraz w plikach widoku można będzie korzystać z pluginu o nazwie serviceManager, który dodaliśmy powyżej, wywoływanego w poniższy sposób:

```
$this->serviceManager()->get(\Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger::class);
```

### Korzystanie z flashMessengera w widoku

By skorzystać z flash messnegera należy utworzyć jego instancję:

```
$this->serviceManager()->get(\Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger::class);
```

By było to możliwe należy dodać jego fabrykę do `module.config.php`:

```
'service_manager' => [
    'factories' => [
        \Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger::class => \Base\Mvc\Plugin\Factory\FlashMessengerFactory::class,
    ],
]
```

### Implementacja AuthMenagera do autoryzacji użytkowników

Najpierw należy napisać własną fabrykę do utworzenia instancji Menagera rozszerzającą `\Base\Services\Auth\AuthenticationServiceFactory`. Wspomniana fabryka z Base ustawia wstępnie odpowiednie adaptery, ale należy je rozszerzyć by wstrzyknąć dane specyficzne dla naszej aplikacji, jak nazwa modelu czy warunki where dla wiersza lub dodatkowe akcje do wykonania jeśli wystąpi określony EVENT. Przykładowy kod:

```
namespace Application\Services\Auth;

class AuthenticationServiceFactory extends \Base\Services\Auth\AuthenticationServiceFactory
{
    public function __invoke(\Interop\Container\ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = parent::__invoke($container, $requestedName, $options);
        
        $adapter = $service->getAdapter();
        /* @var $adapter \Base\Services\Auth\AuthAdapter */
        
        // nazwa modelu
        $adapter->setModelName(\Application\Model\UsersTable::class);
        $adapter->setRowPreConditions(['NOT ghost']);
        $adapter->setRowPostConditions([
            [
                'condition' => ['is_locked' => false],
                'message' => 'User is locked',
            ],
        ]);
        
        $adapter->addCallable(\Base\Services\Auth\AuthAdapter::EVENT_LOGIN_SUCCESS, function(\Base\Services\Auth\AuthAdapter $adapter) {
            $model = $adapter->getModel();
            $row = $adapter->getUserRow();
            $remoteAddress = new \Laminas\Http\PhpEnvironment\RemoteAddress();
            
            $model->update([
                'last_login_time' => new \Laminas\Db\Sql\Expression("NOW()"),
                'last_login_ip_address' => $remoteAddress->getIpAddress(),
            ], [
                'id' => $row->id,
            ]);
        });
        
        return $service;
    }
}
```

Następnie w pliku konfiguracji modułu `module.config.php` należy dodać tę fabrykę by mógł z niej korzystać ServiceManager:

```
    'service_manager' => [
        'factories' => [
            /* ... */
            \Laminas\Authentication\AuthenticationService::class => Services\Auth\AuthenticationServiceFactory::class,
        ],
        'abstract_factories' => [
            \Base\Logic\Factory\AbstractLogicFactory::class,
            \Base\Form\AbstractFormFactory::class,
        ],
    ]
```

Konieczne jest również dodanie konfiguracji do obsługi sesji, z której korzysta AuthManager. Należy to zrobić w pliku `config\autoload\global.php`:

```
return [
    /* ... */
    // Session configuration.
    'session_config' => [
        // Session cookie will expire in 1 hour.
        'cookie_lifetime' => 60*60*1,     
        // Session data will be stored on server maximum for 30 days.
        'gc_maxlifetime'     => 60*60*24*30, 
    ],
    // Session manager configuration.
    'session_manager' => [
        // Session validators (used for security).
        'validators' => [
            \Laminas\Session\Validator\RemoteAddr::class,
            \Laminas\Session\Validator\HttpUserAgent::class,
        ]
    ],
    // Session storage configuration.
    'session_storage' => [
        'type' => \Laminas\Session\Storage\SessionArrayStorage::class,
    ],
];
```

W kontrolerze wystarczy utworzyć instancję Managera i korzystać z gotowych już metod do logowania, rejestracji czy wylogowania. Należy jedynie dopisać odpowiednie formularze.

```
$authManager = $this->getServiceManager()->get(\Base\Services\Auth\AuthManager::class);
/* @var $authManager \Base\Services\Auth\AuthManager */
```

### Implementacja RBAC

Najpierw należy napisać odpowiedniego Managera dla ról pobieranych z bazy danych, który rozszerza tego z Base `\Base\Services\Rbac\RbacRolesManager`, odpowiedniego dla naszej aplikacji, tak żeby nie trzeba było dokładnie kopiować struktury bazy danych, tylko można było to określić samemu wedle potrzeb.

```
namespace Application\Services\Rbac;

class RbacRolesManager extends \Base\Services\Rbac\RbacRolesManager
{
    /**
     * {@inheritdoc}
     */
    public function getRolesData()
    {
        $model = $this->getServiceManager()->get(\Application\Model\RolesTable::class);
        /* @var $model \Base\Db\Table\AbstractModel */
        
        $select = $model->select()
                ->where(['NOT ghost']);
        
        $data = $model->fetchAll($select);
        
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleParentsData($idRole)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleChildrensData($idRole) 
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getRolePermissionsData($idRole)
    {
        $model = $this->getServiceManager()->get(\Application\Model\RolePermissionsTable::class);
        /* @var $model \Base\Db\Table\AbstractModel */
        
        $select = new \Laminas\Db\Sql\Select();
        $select->from(['rp' => new \Laminas\Db\Sql\TableIdentifier('role_permissions', 'public')], [])
                ->join(['p' => new \Laminas\Db\Sql\TableIdentifier('permissions', 'public')], 'p.id = rp.id_permission')
                ->where(['NOT rp.ghost', 'NOT p.ghost', 'rp.id_role' => $idRole]);
        
        $data = $model->fetchAll($select);
        
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserRolesData($idUser)
    {
        $model = $this->getServiceManager()->get(\Application\Model\RolesTable::class);
        /* @var $model \Base\Db\Table\AbstractModel */
        
        $select = new \Laminas\Db\Sql\Select();
        $select->from(['r' => new \Laminas\Db\Sql\TableIdentifier('roles', 'public')])
                ->where(['NOT r.ghost']);
        
        if (!empty($idUser)) {
            $select->join(['ur' => new \Laminas\Db\Sql\TableIdentifier('user_roles', 'public')], 'ur.id_role = r.id', [])
                    ->where(['NOT ur.ghost', 'ur.id_user' => $idUser]);
        } else {
            $select->where(['code' => \Base\Services\Rbac\RbacManager::DEFAULT_ROLE_CODE]);
        }
        
        $data = $model->fetchAll($select);
        
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleByNameRow($name)
    {
        $model = $this->getServiceManager()->get(\Application\Model\RolesTable::class);
        /* @var $model \Base\Db\Table\AbstractModel */
    }
}
```

Oraz napisać odpowiednią fabrykę, która zwróci RbacManagera z Base, przykładowo:

```
namespace Application\Services\Rbac;

class RbacManagerFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{
    public function __invoke(\Interop\Container\ContainerInterface $container, $requestedName, array $options = null)
    {
        $rbacManager = new \Base\Services\Rbac\RbacManager();
        $rbacManager->setServiceManager($container);
        
        $rbacRolesManager = new RbacRolesManager();
        $rbacRolesManager->setServiceManager($container);
        
        $rbacManager->setRolesManager($rbacRolesManager);
        
        $assertionManager = $container->get(\Base\Services\Rbac\RbacAssertionManager::class);
        /* @var $assertionManager \Base\Services\Rbac\RbacAssertionManager */
        $assertionManager->setServiceManager($container);
        
        $rbacManager->addAssertionManager($assertionManager);
        
        return $rbacManager;
    }
}
```

Teraz wystarczy dodać konfigurację fabryki do `module.config.php`:

```
    'service_manager' => [
        'factories' => [
            \Base\Services\Rbac\RbacManager::class => Services\Rbac\RbacManagerFactory::class,
        ],
        'abstract_factories' => [
            \Base\Logic\Factory\AbstractLogicFactory::class,
            \Base\Form\AbstractFormFactory::class,
        ],
    ]
```

Oraz dodać sprawdzanie autoryzacji w `Module.php`:

```
    public function onBootstrap(\Laminas\Mvc\MvcEvent $event)
    {
        $application = $event->getApplication();
        $eventManager = $application->getEventManager();
        
        $eventManager->attach(\Laminas\Mvc\MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch']);
    }
    
    public function onDispatch(\Laminas\Mvc\MvcEvent $event)
    {
        $application = $event->getApplication();
        $serviceManager = $application->getServiceManager();
        
        $controller = $event->getTarget();
        $routeName = $event->getRouteMatch()->getMatchedRouteName();
        $controllerName = $event->getRouteMatch()->getParam('controller', null);
        $actionName = $event->getRouteMatch()->getParam('action', null);
        
        $authManager = $serviceManager->get(\Base\Services\Auth\AuthManager::class);
        /* @var $authManager \Base\Services\Auth\AuthManager */
        
        // sprawdzenie czy użytkownik ma dostęp do zasobu
        $result = $authManager->filterAccess($routeName, $actionName);
        
        switch ($result) {
            case \Base\Services\Auth\AuthManager::ACCESS_DENIED:
                if ($routeName !== 'auth' || $actionName !== 'notauthorized') {
                    return $controller->redirect()->toRoute('auth', ['action' => 'notauthorized']);
                }
                break;
            case \Base\Services\Auth\AuthManager::AUTH_REQUIRED:
                if ($routeName !== 'auth' || $actionName !== 'login') {
                    $controller->flashMessenger()->addErrorMessage('Musisz się zalogować by uzyskać dostęp do tego zasobu');
                    
                    return $controller->redirect()->toRoute('auth', ['action' => 'login']);
                }
                break;
        }
    }
```

Przy braku autoryzacji lub braku dostępu powinno nastąpić odpowiednie przekierowanie.

## Dodatkowe informacje

Tworzenie własnej paczki composera:
https://symfonycasts.com/screencast/question-answer-day/create-composer-package
