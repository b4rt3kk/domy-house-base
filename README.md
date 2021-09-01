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


## Dodatkowe informacje

Tworzenie własnej paczki composera:
https://symfonycasts.com/screencast/question-answer-day/create-composer-package
