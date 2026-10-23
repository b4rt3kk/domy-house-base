# Wybór kolumn tabel paginatora

`Base\Paginator` zachowuje `getHeaders()` jako listę pól dopuszczonych przez konkretny paginator. `getColumnOptions()` odczytuje konfigurację z encji/prototypu modelu; `getVisibleHeaders()` zwraca wyłącznie wybrany podzbiór w kolejności konfiguracji. Wspólny `base/paginator` przekazuje ten podzbiór do `base/table`. W pierwszej kolejności używaj wspólnych widoków, klas i metod Base. Brakującą funkcję dodawaj jako reużywalne rozszerzenie/opcję Base zamiast kopiować implementację w module. Wszystkie tabelaryczne wyniki paginatorów admina muszą korzystać z `base/table`; różnice domenowe konfiguruj przez `ViewConfig::setTableOptions()` i `setTableActionsPartial()`. Partiale komórek/akcji nie mogą powielać struktury tabeli ani nawigacji.

W `AbstractEntity::$headersMapping` każde pole może mieć:

- `title`: etykieta w tabeli i selektorze;
- `defaultVisible`: domyślnie true, false pozwala wybrać pole, ale początkowo je ukrywa;
- `selectable`: domyślnie true, false wyklucza pole z selektora i renderowania przez paginator;
- `escape`: true dla wartości tekstowych wymagających escapowania w `base/table` (np. login).

Pola nieobecne w `headers` konkretnego paginatora nigdy nie są dodawane automatycznie z bazy ani modelu. To granica zakresu listy; widoczność nie zastępuje autoryzacji. Nawet spreparowany zapis sesji nie ujawni wykluczonego pola. Ukryte kolumny nie są renderowane w HTML, a ich słowniki nie są ładowane. Słowniki wyświetlanych kolumn są klonowane przed konfiguracją.

Górna i dolna nawigacja renderują domyślnie zwinięte accordiony Bootstrap „Zaawansowane” z chevronem, obramowaniem i odrębnym nagłówkiem. Identyfikatory collapse są unikalne. Wielokrotny select z etykietą floating jest nad wspólnym dolnym paskiem Zastosuj/Wyczyść. Oba przyciski zajmują pełną szerokość na mobile i desktopie. Wyczyść przywraca ustawienia domyślne. Zmiany synchronizują oba selecty, a jawne zastosowanie pozwala dokonać wielu wyborów przed przeładowaniem. Pusta tablica jest odrzucana przed AJAX i przez endpoint MVC (422, „Wybierz co najmniej jedną kolumnę”). Stare puste preferencje lub wybór bez dopuszczonych kolumn wracają do domyślnych; gdy wszystkie opcje mają defaultVisible=false, widoczna jest pierwsza dopuszczona kolumna. Null przywraca konfigurację domyślną. Błąd AJAX pozostaje przy kontrolce i pozwala ponowić zapis.

Preferencje są osobne dla każdego paginatora i sesji: `getColumnPreferencesStorageName(id)` tworzy nazwę przez SHA-256 identyfikatora (bez niedozwolonych znaków w nazwie kontenera Laminas). Nie są częścią filtra: czyszczenie filtra ich nie usuwa. `getColumnSelectionToken()` tworzy token CSRF w tym samym kontenerze. Konsument endpointu AJAX musi sprawdzić POST, identyfikator, token oraz strukturę listy i zapisać `selection` w tym kontenerze. MVC implementuje ten kontrakt w `AjaxController::paginatorAction`; Base nie tworzy endpointu ani bibliotek JS.

Selektor używa istniejącego jQuery i opcjonalnego bootstrap-select (`selectpicker-select-filter`); bez pluginu pozostaje natywnym selectem multiple. Dla aktywnego pluginu synchronizacja używa `selectpicker('val', ...)`, nie przebudowy listy przez refresh. Responsywność admina należy do CSS MVC.

Walidacja: `tests/PaginatorColumnsTest.php` z autoloaderem MVC, `--stderr`; testy endpointu MVC oraz `tools/check-admin-libraries.py --base-view-root ../domy-house-base/src/view --paginator-screenshot-dir /tmp/paginator-columns`. Sprawdź domyślne/ukryte/niedostępne pola, pusty wybór, reset, dwie listy, top/bottom, awarię i CSRF. Publikacja wymaga najpierw Base, potem aktualizacji locka MVC.


Walidacja pustego wyboru: komunikat `data-columns-error` znajduje się bezpośrednio
po polu, przed akcjami. Native select i widoczny przycisk bootstrap-select otrzymują
is-invalid, aria-invalid i aria-describedby wskazujące unikalny ID komunikatu.
Błąd 422 z serwera korzysta z tej samej ścieżki. Zmiana wyboru/reset usuwa stan
błędu na obu paskach, zachowując inne opisy ARIA. Błąd transportu/zapisu jest
osobnym komunikatem ogólnym `data-columns-save-error` przy akcjach; nie oznacza
niepoprawnej wartości. Adapter admina utrzymuje stan przy refresh, a CSS admina
dodaje ramkę i ikonę błędu do widocznego przycisku pluginu.

Konfiguracja `tableOptions` pochodzi wyłącznie z zaufanego kodu PHP: `showIndex`
(domyślnie true), `class` (dodatkowe klasy tabeli), `actionsLabel` (domyślnie Actions),
`rowClass($row)` (klasy wiersza), `cellPartials` (mapa nazwa kolumny → partial zawartości komórki,
bez td) oraz `context($view, $headers)` (dane pobierane raz na tabelę, tylko dla
widocznych kolumn). Partial komórki otrzymuje row, header, value i context oraz
odpowiada za escapowanie. Dla skonfigurowanej kolumny zastępuje słownik/formatowanie
standardowe; bez niego istniejący kontrakt encji pozostaje bez zmian.
Jawny tableActionsPartial włącza kolumnę akcji także przy pustym rowActions.
Base nadal zarządza nagłówkami, widocznością, sortowaniem, responsywnością i datami
nowrap. Nie serializuj callbacków konfiguracji ani nie przyjmuj ich z requestu.
