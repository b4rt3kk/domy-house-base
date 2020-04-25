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

## Dodatkowe informacje

Tworzenie własnej paczki composera:
https://symfonycasts.com/screencast/question-answer-day/create-composer-package