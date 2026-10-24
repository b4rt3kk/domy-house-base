# Akcje masowe tabel

Mechanizm jest opt-in na encji `AbstractEntity`: `allowMassActions=true`,
`massActions` oraz `massEditableFields`. Domyślnie jest wyłączony. Konfiguracja
pochodzi wyłącznie z kodu serwera. `clearRedundantData()` usuwa ją razem
z pozostałymi metadanymi prezentacji przy przygotowywaniu wierszy do cache.

## Tabela i konfiguracja

`massActions` jest mapą klucz akcji → `label`, `route`, `action`, opcjonalne
`params`/`query`, wymagane bazowe `privilege` oraz lista `recordPrivileges`.
Uprawnienie bazowe i przynajmniej jeden tryb rekordowy muszą być dostępne.
Tabela dodatkowo sprawdza tryby dla danych każdego wiersza. Brak dostępu do
wszystkich akcji wyłącza checkbox; akcja obejmująca niedozwolony wiersz jest
odrzucana w UI i musi być ponownie sprawdzona w backendzie. Kluczem zaznaczenia
jest dodatnie `id`, zgodne z konwencją modeli aplikacji.

`base/table` renderuje ukrytą pierwszą kolumnę i wspólny dolny pasek akcji.
Opcjonalne `page_navigation` (przekazywane także przez `base/paginator`) renderuje
standardowe `base/navigation` obok „Akcje masowe” i przycisków modelu.
Przełącznik ma wariant outline-secondary, wszystkie przyciski standardowy padding.
Przyciski operujące na selekcji są w osobnej, początkowo ukrytej grupie
`base-mass-action-group` z ramką i etykietą „Wykonaj dla zaznaczonych”
(`role=group`, dostępna nazwa). Nawigacja strony i przełącznik pozostają poza grupą.
Na desktopie przyciski mają wspólny środek w pionie; konsument zapewnia niewielki
padding ramki i pionowy układ na mobile. Zamknięcie trybu ukrywa całą grupę.
Licznik jest pod przyciskami, ma mniejszy tekst i pogrubioną samą liczbę.
Paginator zachowuje nawigację strony także przy braku wyników i własnej liście.
Nie dodawaj drugiego paska nawigacji w widoku korzystającym z tej opcji.
Natywny checkbox nagłówka używa `indeterminate`, zaznacza tylko dostępne wiersze
bieżącej strony i jest niezależny od wyboru kolumn. Zamknięcie trybu, zmiana
strony, sortowania, filtra lub przeładowanie kasują wybór. Stan jest lokalny dla
instancji tabeli, bez sesyjnego „zaznacz wszystko”. Brak wyboru pokazuje trwały
alert i przenosi do niego fokus. Checkboxy mają etykiety ARIA; konsument dostarcza
CSS `.base-mass-cell`/`.base-mass-choice` z wyśrodkowaniem i celem dotykowym 44 px.

`ViewConfig::setAllowMassActions(false)` wyłącza mechanizm dla konkretnego
paginatora; `base/paginator` przekazuje opcję do tabeli. Wybór kolumn, sortowanie,
filtr oraz górna/dolna nawigacja pozostają niezależne. Przycisk otwiera formularz
przez GET z `ids` jako listą rozdzieloną przecinkami, bez zapisu danych.
`Selection::ids()` akceptuje najwyżej 100 dodatnich identyfikatorów mieszczących
się w PHP_INT_MAX (obecny maksymalny rozmiar strony), odrzuca niepoprawne typy,
usuwa duplikaty i sortuje klucze. Scalar payload unika `max_input_vars`.

## Formularz

`MassEditForm::setFields()` przyjmuje `massEditableFields`: specyfikację Laminas
`element`, opcjonalną jawną specyfikację `input` oraz `nullable`. Konsument może
rozwiązać słownik przed przekazaniem konfiguracji. Każde pole ma selektor
`change_<kolumna>`: keep/set oraz clear tylko dla nullable. Nie wnioskuj zmiany
z pustej wartości. `getChanges()` wywołuj tylko po poprawnej walidacji; zwraca
wyłącznie wskazane pola, a clear zwraca NULL.

Przyciski formularza używają standardowych helperów Base: najpierw zapis
z dolnym marginesem, następnie anulowanie. Oba zajmują pełną szerokość.

Formularz zachowuje CSRF. Nie używa generycznego `AbstractForm::initInputFilter`,
który zastępuje specyfikacje elementów. Jawne `input` zastępuje, a nie scala
łańcuch walidatorów: inaczej DateTimeLocal zachowuje dodatkowy walidator
wymagający sekund. Pozostałe elementy zachowują domyślne walidatory, w tym tryby.
Walidowane są wskazane wartości, wszystkie tryby, ids i CSRF. Nieprawidłowe typy
są odrzucane i czyszczone przed renderem; tablica w pojedynczym selekcie inaczej
powoduje wyjątek renderera. Nieznane pola nie przechodzą do zmian.

`base/form` inicjalizuje tylko formularze z `data-mass-edit`: keep/clear wyłącza
pole wartości, set włącza je i ustawia required, również w selectpickerze.
Bez JS serwer zachowuje ten sam kontrakt. Skrypt blokuje powtórny submit;
`pageshow` odblokowuje formularz po powrocie z bfcache. Widok konsumenta pokazuje
błędy ukrytych ids/CSRF jako komunikaty ogólne, a błędy wartości przy polach.

## Atomowy zapis

`AbstractModel::updateMass()` wymaga ID użytkownika, callbacku uprawnień i
callbacku walidacji każdego rekordu. Ogranicza zmiany do allowlisty encji.
Transakcja pobiera wszystkie rekordy parametryzowanym SELECT-em z jawnymi
kolumnami i `ORDER BY <primaryKey> FOR UPDATE` (PostgreSQL). Dodatkowe kolumny
kontekstu podaje zaufany kod. Wymagane są standardowe kolumny ghost, autora,
`changed_by` i `changed_at`. Wszystkie rekordy muszą istnieć, nie być ghost i
przejść kontrolę uprawnień/reguł przed pierwszym zapisem. Jeden UPDATE,
sprawdzenie liczby zmienionych rekordów i rollback przy błędzie zapewniają
atomowość. Cache jest czyszczony po commit; oryginalna flaga cache wraca również
po błędzie. Nie uruchamiaj tej metody wewnątrz już otwartej transakcji.

`Rejection` oznacza bezpieczny komunikat operacyjny, `FieldException` wskazuje
pole formularza. Nie ujawniaj komunikatów wyjątków infrastruktury. Nie wywołuj
masowo pełnego zapisu pojedynczego formularza, jeżeli usuwa/odtwarza relacje
lub wykonuje skutki uboczne nieobjęte konfiguracją masową.

## Walidacja i publikacja

`tests/MassActionsTest.php` obejmuje selekcję, allowlistę, CSRF, tryby pól,
SQL/parametry/blokady, walidację przed zapisem, audyt, commit i rollback.
MVC ma bootstrap `tools/test-with-local-base.php` sterowany `BASE_SOURCE_ROOT`
oraz `tools/check-admin-mass-actions.py --base-source-root ../domy-house-base/src`.
Test przeglądarkowy renderuje rzeczywiste partiale i sprawdza wybór, uprawnienia,
błędy/fokus, izolację tabel, URL, pola i responsywność; `--screenshot-dir`
zapisuje podglądy. Zachowaj regresje paginatora i bibliotek admina.

Publikacja wymaga zatwierdzonego Base na origin/master, następnie aktualizacji
`bwiechnik/domy-house-base` i locka konsumenta. Lokalny checkout nie zastępuje
vendor. Nie zapisuj lokalnego path ani nieopublikowanego SHA w composer.lock.
