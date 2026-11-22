# Akcje i domyślna nawigacja wierszy

`table.phtml` przyjmuje zaufane opcje `actionsPresentation=dropdown`,
`defaultAction` (definicja `Base\Navigation\Page\Mvc`) i opcjonalne `rowActions`.
Ustawia je aplikacja dla swojego admina; domyślność Base poza adminem pozostaje
bez zmian. MVC dokumentuje katalog odczytowych podglądów i RBAC w swoim
`docs/agent/admin-row-actions.md`.

`defaultAction` wskazuje rzeczywistą odczytową akcję kontrolera właściciela
listy. Base współdzieli renderowanie i obsługę kliknięcia; nie zastępuje
domenowych tras globalnym endpointem przyjmującym model/listę przez query.
W MVC dodanie podglądu wymaga także wpisu w katalogu, własnego RBAC i ukrytego
dziecka listy w breadcrumbs. Pełna procedura należy do dokumentu MVC wskazanego
powyżej. Zachowuj specjalistyczne podglądy już istniejące w aplikacji.

- Paginator wybiera `ViewConfig.tableActionsPartial`, następnie historyczne
  `Paginator.tableActionsPartial`, potem renderer encji. Nie ignoruj starszej
  właściwości: istniejące listy nadal ją deklarują.
- Dropdown to jedno dostępne menu także dla pojedynczej akcji; pusty zestaw
  nie pokazuje przycisku. Unikatowe ID wiąże przycisk i aria-labelledby.
  Stała strategia Poppera zapobiega obcinaniu w kontenerze tabeli.
- Własne partiale otrzymują `items_only` i `default_action`. W menu zwracają
  same `a.dropdown-item`, bez opakowań lub powielania akcji domyślnej. Poza
  tym trybem zachowują dotychczasową prezentację. Parametr `content` pozwala
  złożyć wspólne menu z wcześniej wyrenderowanych, zaufanych partiali.
- W trybie menu kontroluj bazowe uprawnienie trasy oraz rekordowe, a także
  warunki `where`. Nie rejestruj modali niedostępnych akcji. Deduplikuj rzeczywiste
  docelowe URL przed zamianą linku na trigger `#`; inaczej zostaje tylko pierwsze
  potwierdzenie. Escapuj href, etykiety i atrybuty.
- Dozwolony `defaultAction` daje `data-row-href`, fokus i Enter. Nie przejmuj
  linków, kontrolek, menu, pól akcji masowych, kliknięć z modyfikatorami ani
  zaznaczania tekstu. Jawny link pozostaje dostępny bez JS i do otwarcia nowej karty.
  Aplikacja używa tu wyłącznie odczytowego „Pokaż”, nigdy mutacji lub edycji.
- `base/show` ma opcjonalne `escape_values=true`: koduje także etykiety wartości
  ze słowników przed formatowaniem. Ładuje wyłącznie słowniki wybranych pól,
  klonując usługę przed konfiguracją. Brak tej opcji zachowuje dotychczasowy
  kontrakt HTML innych odbiorców. Opis nie jest skracany przez FORMAT_TRUNCATE.

Walidacja z MVC: `AdminRowActionsViewTest`, `AdminRecordPreviewTest` oraz
`tools/check-admin-row-actions.py --base-view-root ../domy-house-base/src/view`.
Test przeglądarkowy używa rzeczywistych partiali i Bootstrap 5 przy czterech
szerokościach; nie wymaga publikowania Base ani edycji vendor. Po akceptacji
opublikuj Base, następnie odśwież `composer.lock` MVC do dokładnej rewizji.
