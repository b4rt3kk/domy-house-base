# Uwierzytelnianie, wildcard RBAC i eksport CSV

- `AuthAdapter` odrzuca `is_virtual` przed weryfikacją hasła. Konto techniczne nie
  może mieć sesji użytkownika, także przy poprawnym haśle. Wynik błędu nie zawiera
  tożsamości i wywołuje tylko zdarzenie nieudanego logowania.
- Google OAuth sprawdza konto istniejące i wszystkie `rowPostConditions` przed
  przypisaniem UID/dostawcy lub wywołaniem sukcesu. OAuth nie jest wyjątkiem od
  blokady, aktywności ani technicznego charakteru konta.
- Aplikacja konfiguruje postconditions i usuwa dawne techniczne sesje. Biblioteka
  nie zastępuje kontroli tożsamości przy odczycie JWT przez konsumenta.
- `RbacRolesManager` dopasowuje wildcard do całej nazwy uprawnienia. Cytuje znaki
  regex, a tylko `*` staje się wieloznacznikiem. Nie używaj klucza/indeksu tablicy
  jako nazwy uprawnienia. Do `ResultSet::initialize` przekazuj tablicę tablic danych, zachowując prototyp
  encji. Encja Base nie jest Countable, a Laminas liczy pola pierwszego wiersza.
  Testuj zarówno ArrayObject, jak i rzeczywisty kontrakt AbstractEntity.
- CSV korzysta z `getConfiguredModel`; nie przesłaniaj publicznego `getModel`
  rodzica metodą chronioną o niezgodnej sygnaturze. Eksport zachowuje separator,
  cytowanie i możliwość podania danych lub modelu.
- Regresje: `tests/P1SecurityTest.php`, uruchamiane PHPUnit z autoloaderem
  konsumenta MVC. Testy korzystają z rzeczywistych klas Base, atrap OAuth/modeli
  oraz syntetycznych haseł; nie kontaktują się z zewnętrznym dostawcą.
