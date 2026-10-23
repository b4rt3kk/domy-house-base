# Wybór kolumn tabel paginatora

`Base\Paginator` zachowuje `getHeaders()` jako listę pól dopuszczonych przez konkretny paginator. `getColumnOptions()` odczytuje konfigurację z encji/prototypu modelu; `getVisibleHeaders()` zwraca wyłącznie wybrany podzbiór w kolejności konfiguracji. Wspólny `base/paginator` przekazuje ten podzbiór do `base/table`. Listy z własnym `listPartial` (np. karty) zachowują dotychczasowy kontrakt i nie dostają selektora kolumn.

W `AbstractEntity::$headersMapping` każde pole może mieć:

- `title`: etykieta w tabeli i selektorze;
- `defaultVisible`: domyślnie true, false pozwala wybrać pole, ale początkowo je ukrywa;
- `selectable`: domyślnie true, false wyklucza pole z selektora i renderowania przez paginator;
- `escape`: true dla wartości tekstowych wymagających escapowania w `base/table` (np. login).

Pola nieobecne w `headers` konkretnego paginatora nigdy nie są dodawane automatycznie z bazy ani modelu. To granica zakresu listy; widoczność nie zastępuje autoryzacji. Nawet spreparowany zapis sesji nie ujawni wykluczonego pola. Ukryte kolumny nie są renderowane w HTML, a ich słowniki nie są ładowane. Słowniki wyświetlanych kolumn są klonowane przed konfiguracją.

Górna i dolna nawigacja renderują domyślnie zwinięte accordiony Bootstrap „Zaawansowane” z chevronem, obramowaniem i odrębnym nagłówkiem. Identyfikatory collapse są unikalne. Wielokrotny select z etykietą floating jest nad wspólnym dolnym paskiem Zastosuj/Wyczyść. Oba przyciski zajmują pełną szerokość na mobile i desktopie. Wyczyść przywraca ustawienia domyślne. Zmiany synchronizują oba selecty, a jawne zastosowanie pozwala dokonać wielu wyborów przed przeładowaniem. Pusta tablica jest odrzucana przed AJAX i przez endpoint MVC (422, „Wybierz conajmniej jedną kolumnę”). Stare puste preferencje lub wybór bez dopuszczonych kolumn wracają do domyślnych; gdy wszystkie opcje mają defaultVisible=false, widoczna jest pierwsza dopuszczona kolumna. Null przywraca konfigurację domyślną. Błąd AJAX pozostaje przy kontrolce i pozwala ponowić zapis.

Preferencje są osobne dla każdego paginatora i sesji: `getColumnPreferencesStorageName(id)` tworzy nazwę przez SHA-256 identyfikatora (bez niedozwolonych znaków w nazwie kontenera Laminas). Nie są częścią filtra: czyszczenie filtra ich nie usuwa. `getColumnSelectionToken()` tworzy token CSRF w tym samym kontenerze. Konsument endpointu AJAX musi sprawdzić POST, identyfikator, token oraz strukturę listy i zapisać `selection` w tym kontenerze. MVC implementuje ten kontrakt w `AjaxController::paginatorAction`; Base nie tworzy endpointu ani bibliotek JS.

Selektor używa istniejącego jQuery i opcjonalnego bootstrap-select (`selectpicker-select-filter`); bez pluginu pozostaje natywnym selectem multiple. Dla aktywnego pluginu synchronizacja używa `selectpicker('val', ...)`, nie przebudowy listy przez refresh. Responsywność admina należy do CSS MVC.

Walidacja: `tests/PaginatorColumnsTest.php` z autoloaderem MVC, `--stderr`; testy endpointu MVC oraz `tools/check-admin-libraries.py --base-view-root ../domy-house-base/src/view --paginator-screenshot-dir /tmp/paginator-columns`. Sprawdź domyślne/ukryte/niedostępne pola, pusty wybór, reset, dwie listy, top/bottom, awarię i CSRF. Publikacja wymaga najpierw Base, potem aktualizacji locka MVC.
