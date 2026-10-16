# Podgląd pojedynczego rekordu

`src/view/partials/show.phtml` jest partialem `base/show` używanym m.in. przez
panel MVC dla wiadomości, użytkowników, zamówień i płatności. Nie jest listą
wyników (`table.phtml`). Zachowuje mapowanie nagłówków, słowniki, formatowanie
oraz istniejący kontrakt HTML wartości; zmiana układu nie może zmieniać escapowania.

Klasy `record-details`, `record-details-label`, `record-details-value` są
kontraktem stylowania. Base nie wymusza mobilnego układu w publicznych layoutach.
MVC ładuje własny `admin-responsive.css` wyłącznie w panelu: poniżej 768 px
etykieta jest mniejsza, uppercase, nad pełnoszeroką wartością. Od 768 px pozostają
dwie kolumny. Tabele list wyników pozostają poziome.

Walidacja: lint PHP oraz test przeglądarkowy MVC
`python3 tools/check-message-center.py --base-view-root ../domy-house-base/src/view`.
Test korzysta z faktycznego partiala z tej ścieżki bez nadpisywania vendor,
obejmuje długie teksty i granicę breakpointu. Po publikacji Base należy odświeżyć
zależność i `composer.lock` w MVC przed budową obrazu; lokalna zmiana Base sama
nie zmienia pakietu instalowanego przez Composer.
