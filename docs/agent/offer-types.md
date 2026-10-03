# Granica Base a typ oferty

- Typy oferty, dopuszczalne kategorie, lokalizacje i katalogi płatności należą do `Application\Logic` w MVC/API. Nie dodawaj reguł domenowych 1/3 do wspólnego Base.
- `Base\Dictionary` jest stanowy. Nowy odczyt `offer_type` powinien używać klonu z `init()` oraz właściwego code słownika, języka i identyfikatora wpisu; nie zastępuj identyfikatora domenowego ID wiersza dictionary_entry. Publiczną allowlistę 1/3 egzekwuje aplikacja, nie słownik.
- Generic persistence usuwa false przy insert i może zamienić false na NULL przy update. Aplikacja zapisuje jawne SQL TRUE/FALSE dla promowania; przywrócenie produktu nie jest powodem do ubocznej zmiany globalnej serializacji.
- Analiza przywrócenia typu nie wykazała potrzeby zmian runtime Base. Plan i źródła są w repozytorium MVC: `docs/plans/offer-type-restoration.md` oraz `docs/agent/offer-types.md`. Jeśli przyszła implementacja jednak zmieni Base, wymagane jest zatwierdzone wydanie Base i aktualizacja composer.lock MVC zgodnie z jego instrukcjami.
