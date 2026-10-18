# Kontrakty widoków Base a aktualizacja bibliotek admina

Stan implementacji 2026-10-18. Base jest biblioteką
PHP i partiali, nie osobnym panelem ani właścicielem dystrybucji Bootstrap/jQuery.
Layout i biblioteki przeglądarkowe należą do aplikacji MVC. W MVC mapa
`view_manager.template_map` wskazuje pliki z zainstalowanego pakietu Composer,
nie lokalnego checkoutu Base. Nie naprawiać tego przez edycję vendor.

## Struktura i kontrakty

- `src/view/partials/paginator.phtml` składa filtr, górną/dolną nawigację
  i listę zgodnie z `Base\Config\Paginator\ViewConfig`.
  Domyślna lista to `base/table`; własne moduły mogą ustawić `setListPartial`.
- `paginator_filter.phtml` używa accordionu BS5 z `data-bs-toggle/target`.
  Zachować inicjalizację danych/filtra przez kontroler i obsługę błędów.
- `paginator_navigation.phtml` oraz delegujący do niego wariant bottom mają
  kontrakt opisany w [paginator-navigation.md](paginator-navigation.md).
  Skrypt inline w `paginator.phtml` używa jQuery AJAX POST do zapisu
  `items_per_page` z `paginatorId`, następnie przeładowuje stronę.
- `table.phtml` renderuje nagłówki, wartości i akcje; inline JS obsługuje
  sortowanie AJAX. Inicjalizuje `ajaxParams` przez natywne `JSON.parse`.
  PHP koduje literały URL/JSON przez `json_encode` z flagami JSON_HEX_*;
  cudzysłowy i zamknięcie script nie mogą przerwać kodu. Zmienne są izolowane
  w funkcji, aby wiele tabel nie powodowało kolizji globalnych let/const.
  Nie przywracać `$.parseJSON` (usunięte w jQuery 4). Zachować payload endpointu.
- `table_actions.phtml`, `table_actions_dropdown.phtml`, `navbar.phtml`
  i `modals/confirm.phtml` używają `data-bs-*`. MVC mapuje modal jako
  `base/modal/confirm`. Nie zastępować ich globalnie atrybutami BS4 tylko
  na potrzeby historycznego motywu. Aktualny admin MVC używa jednego Bootstrap 5.
- Historyczny modal potwierdzenia ma link GET; nie zmieniać przy migracji
  potwierdzanej nawigacji w mutację. Usuwanie wymaga osobnego POST/CSRF,
  zgodnie z kontraktem kontrolera i uprawnień aplikacji.
- `form.phtml`, `form/fieldset.phtml`, `form/element.phtml` odpowiadają za
  renderowanie formularza, etykiet, błędów, kontenerów i przycisków.
  `.form-element-container` jest selektorem logiki pól w MVC; zachować go.
  W katalogu jest też `form_responsive.phtml`; jego istnienie nie oznacza,
  że zastępuje mapowanie `base/form` w aplikacji.
- `show.phtml` renderuje szczegóły, nie tabelę wyników. Klasy
  `record-details*` i kontrakt HTML: [record-details.md](record-details.md).

## Granice zmian

Partiale Base mogą mieć odbiorców poza adminem. Aktualizacja panelu nie
upoważnia do globalnej zmiany frontu. Naprawy API JS powinny zachowywać
kompatybilność z obecnymi konsumentami; zmiany wyłącznie wizualne preferują
adminowy CSS/renderer w aplikacji. Nie dodawać do Base globalnego ładowania
Bootstrap/jQuery, drugiej instancji jQuery ani obejścia `$.parseJSON` na stałe.

Samo przejście na Bootstrap 5 nie pozwala usunąć jQuery: tabela i paginator
nadal go wymagają. Wydanie slim bez AJAX jest niezgodne z tym kontraktem.
Zmiana parsera powinna uwzględniać osadzenie JSON w HTML/JS; nie wykonywać
mechanicznej podmiany z pogorszeniem escapowania lub walidacji.

Plan migracji samego motywu, selectpickera, CKEditora i wykresów znajduje się
w dokumentacji właściciela MVC: `docs/agent/admin-library-upgrade.md`.
Po zaakceptowanej zmianie Base należy opublikować jego zatwierdzoną rewizję,
a następnie odświeżyć zależność i `composer.lock` MVC. Lokalna zmiana źródła
Base nie zmienia automatycznie pakietu używanego przez aplikację/obraz.

## Walidacja

Z katalogu MVC można testować bieżący checkout Base bez edycji vendor:

```sh
python3 tools/check-admin-mobile-navigation.py --base-view-root ../domy-house-base/src/view
python3 tools/check-message-center.py --base-view-root ../domy-house-base/src/view
python3 tools/check-admin-libraries.py --base-view-root ../domy-house-base/src/view
```

Testy czytają aktualny manifest bibliotek admina MVC. Test integracyjny
sprawdza realne partiale Base: sortowanie, payload/jeden POST, dwa paski
wielkości strony i wiele tabel na stronie. Obejmuje JSON z cudzysłowami,
backslashem, ampersandem i zamknięciem script, bez modyfikowania vendor.
Mockuje AJAX, więc nie dowodzi działania sesji/endpointów z rzeczywistą bazą.
Nie stanowi także gwarancji zgodności wszystkich pluginów z jQuery 4.
Zmienione PHP sprawdzać składniowo, uruchamiać odpowiednie testy aplikacji
i `git diff --check`.

Źródło usunięcia API: [jQuery 4 upgrade guide](https://jquery.com/upgrade-guide/4.0/).
