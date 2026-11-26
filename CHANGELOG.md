# Changelog

Opis zmian tego repozytorium. 2.0.0 jest uzgodnionym punktem startowym obecnego
stanu, nie rekonstrukcją historii 1.x ani deklaracją napraw otwartych issue audytu.

## [Unreleased]

## [2.1.0] - 2026-11-27

### Bezpieczeństwo

- Odrzucono logowanie kont technicznych hasłem i Google; OAuth respektuje blokadę i aktywność konta ([#46](https://github.com/b4rt3kk/domy-house-base/issues/46)).
- Naprawiono dopasowanie pełnej nazwy uprawnienia i cytowanie wildcard RBAC ([#47](https://github.com/b4rt3kk/domy-house-base/issues/47)).

### Naprawiono

- Przywrócono eksport CSV bez konfliktu widoczności metody z rodzicem ([#48](https://github.com/b4rt3kk/domy-house-base/issues/48)).
- Zapewniono poprawną hydratację uprawnień wildcard dla encji aplikacji bez interfejsu Countable.
- Dodano regresje hasła, OAuth, zakresów wildcard i eksportu danych/modelu.

Część wspólnego wydania aplikacji 2.1.0. Konsumenci muszą zaktualizować lockfile.

## [2.0.0] - 2026-11-26

### Zmieniono

- Ustanowiono punkt odniesienia 2.0.0 bieżącej biblioteki, bez zmian jej logiki.
- Dodano zasady SemVer i utrzymywania changelogu.
- Usunięto historyczne statyczne pole version z composer.json; wersję określa tag VCS.

Zadanie: #51.

