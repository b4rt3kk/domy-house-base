# Wersjonowanie i changelog

Czytaj przed zmianą kodu, przygotowaniem wydania, tagowaniem lub deploymentem.
Stosujemy [SemVer 2.0.0](https://semver.org/lang/pl/) i niezmienne tagi `vX.Y.Z`.

## Decyzja agenta

- Agent sam klasyfikuje zakres i krótko uzasadnia proponowany numer w podsumowaniu
  oraz opisie PR. Wielkość diffu, czas pracy i liczba commitów nie określają wersji.
- PATCH (`2.0.0 -> 2.0.1`): zgodna naprawa błędu, zabezpieczenie lub optymalizacja
  zachowująca kontrakt. MINOR (`2.0.0 -> 2.1.0`): zgodna nowa funkcja,
  istotne rozszerzenie lub zapowiedź wycofania funkcji. MAJOR (`2.x.y -> 3.0.0`):
  świadoma zmiana niezgodna wstecz. Duży refactoring bez zmiany kontraktu nie
  wystarcza do MAJOR.
- Kontrakt obejmuje API, formaty danych, integracje, konfigurację, zachowanie
  dostępne użytkownikowi i możliwość współpracy wersji podczas wdrożenia.
  Nowa migracja sama w sobie nie wymusza MAJOR; oceń jej kompatybilność i rollback.
  Zmiana niezgodna wymaga opisania migracji konsumentów i ryzyka.
- Przy kilku zmianach wybierz najwyższy wymagany poziom dla całego wydania.
  MINOR zeruje PATCH, MAJOR zeruje MINOR i PATCH.
- Każda istotna zmiana otrzymuje wpis w `CHANGELOG.md` w sekcji `Unreleased`,
  w tej samej zmianie co implementacja. Samo docs/test/refactoring bez wpływu na
  wydawany produkt nie wymaga osobnego nowego numeru.
- Numer ustalaj raz przy przygotowaniu wydania względem ostatniego opublikowanego
  tagu. Nie podbijaj go na każdy commit, poprawkę PR ani etap test/preprod/prod.
  Jeżeli niewydany zakres rośnie, przelicz docelowy numer od ostatniego wydania,
  zamiast inkrementować poprzednią propozycję.
- Sprawdź origin i aktualną linię integracji przed finalizacją, aby równoległe
  zadania nie zarezerwowały dwóch różnych wydań pod tym samym numerem.
- Klasyfikacja i lokalna aktualizacja dokumentacji nie są stałą zgodą na
  commit/push/merge/tag/deploy. Obowiązują zgody i przepływy z AGENTS.md.
  Nie pytaj ponownie o operacje już objęte jawnym zleceniem użytkownika.

## Treść changelogu

- Stosuj `Unreleased`, a następnie `## [X.Y.Z] - YYYY-MM-DD`, najnowsze na górze.
  Data jest datą wydania, a nie dowolnego commita; przed publikacją sprawdź ją.
- Grupuj tylko niepuste sekcje: Dodano, Zmieniono, Naprawiono, Bezpieczeństwo,
  Wycofano/Usunięto. Opisuj rezultat dla użytkownika/utrzymania, podaj issue/PR.
- Zaznacz migracje, konfigurację, niezgodności i ograniczenia rollbacku, gdy występują.
  Nie kopiuj logu Git, sekretów, danych środowisk ani nieprzeanalizowanej historii.
- Do sekcji wydania przenieś tylko rzeczywiście włączone zmiany. Nie deklaruj
  wdrożenia przed jego sukcesem. Nie dopisuj niewydanych poprawek pod starym tagiem.
- `2.0.0` jest świadomie uzgodnionym punktem startowym aktualnego stanu;
  nie oznacza naprawienia wszystkich otwartych issue ani odtworzonej historii 1.x.

## Tagi i odtworzenie

- Tagi są opisane, niezmienne i wskazują dokładny zaakceptowany commit integracji.
  Nie przesuwaj, nie usuwaj i nie nadpisuj istniejącego tagu; konflikt zatrzymuje wydanie.
- Dla wydania aplikacji zapisz zestaw rewizji wszystkich repozytoriów, również
  komponentów bez zmiany kodu. Tagi wspólnego wydania identyfikują ten zestaw.
- Tag odtwarza źródła, nie stan danych. Zachowaj lockfile zależności, obrazy,
  manifest wdrożenia i procedurę odtworzenia bazy/uploadów. Nie wykonuj
  `reset --hard`, force-push ani automatycznego rollbacku bazy na podstawie tagu.

## Biblioteka

- Composer wyznacza wersję z tagu Git; nie dodawaj statycznego pola version do
  composer.json. Nazwa pakietu i kontrakty autoload pozostają bez zmian.
- Opisz zmiany kontraktów PHP, zależności i wymaganej wersji interpretera.
  Konsument musi zablokować dokładną zaakceptowaną rewizję w composer.lock.
- Sam tag nowej wersji nie aktualizuje zainstalowanego pakietu u konsumentów.
  Zmiany biblioteki wymagają skoordynowanej aktualizacji lockfile i walidacji.
- Przy wydaniu obejmującym wiele repozytoriów właściciel aplikacji utrzymuje
  zbiorczy numer i zestaw rewizji; ten changelog opisuje tylko bibliotekę.
  Nie publikuj tu prywatnych szczegółów aplikacji korzystających z pakietu.

