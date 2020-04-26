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

Teraz należy wywołać komendę do instalacji paczki:

```
composer require --dev bw/base:master
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

## Dodatkowe informacje

Tworzenie własnej paczki composera:
https://symfonycasts.com/screencast/question-answer-day/create-composer-package