# Walidacja formularzy i prezentacja błędów

## Obowiązujący kontrakt

- API jest autorytatywne. Angular sprawdza te same publiczne reguły przed wysłaniem: wymagane pola, normalizację, długość, format, zakresy, dopuszczalne opcje i zależności warunkowe. Uprawnienia, własność zasobów, dostępność słowników, CAPTCHA i dekodowanie plików są ponownie sprawdzane na serwerze.
- Przy zmianie reguły sprawdź obie warstwy i testy graniczne. Nie obniżaj wymagań API tylko dlatego, że frontend ich nie zna. Długość tekstu licz po obcięciu skrajnych białych znaków, w znakach Unicode, nie bajtach ani jednostkach UTF-16. Escaping HTML należy wykonywać po walidacji długości.
- Błędy danych wejściowych zwracaj jako HTTP 422 i `{ "error": "Validation failed", "messages": { "field": { "code": "komunikat" } } }`. Zachowuj wszystkie komunikaty. Kolekcje używają ścieżek `itemAttributes.<indeks przesłanego wiersza>.value`; indeks nie zastępuje identyfikatora atrybutu.
- Nie zwracaj szczegółów wyjątków, SQL, danych połączenia ani treści żądania. Błędy infrastruktury to błędy ogólne; użytkownik nie powinien poprawiać pola z powodu HTTP 5xx.

## Prezentacja i zachowanie

- Błędy pól pokazuj wyłącznie przy odpowiadających im kontrolkach; nie dodawaj zbiorczego podsumowania ani duplikatów nad formularzem. Błędy ogólne, nieznanych, ukrytych lub nieaktywnych pól bez miejsca przy kontrolce zachowuj jako pojedyncze komunikaty `alert alert-danger`, `role="alert"`. Pusty renderer nie może zajmować miejsca w układzie.
- Przy polu pokazuj wszystkie jego komunikaty jako listę `ul/li`, `role="list"`, bez punktorów i lewego wcięcia. Błędy lokalne oraz błędy API korzystają z tego samego stanu i prezentacji; toast nie zastępuje trwałego komunikatu.
- Nie pokazuj błędów pustego formularza od razu. Pokazuj je po interakcji (`touched`) lub próbie wysłania. Nie wyłączaj przycisku tylko dlatego, że formularz jest niepoprawny: próba wysłania ujawnia błędy, bez wysyłania żądania.
- Po nieudanej próbie ustaw fokus na pierwszą błędną kontrolkę tego formularza i przewiń do niej; przy błędzie ogólnym skup jego pojedynczy komunikat. Nie wybieraj kontrolki z sąsiedniego formularza. Oznacz błędne kontrolki `aria-invalid` i połącz z listami przez `aria-describedby`, zachowując istniejące opisy pomocnicze. Dotyczy to również własnych komponentów wyboru.
- Edycja usuwa nieaktualny błąd danego pola z listy przy kontrolce; kolejna próba ponownie waliduje. Zachowuj dane i wybrane pliki po niepowodzeniu. Blokuj podwójne wysłanie w trakcie żądania.
- Odpowiedź API może zawierać obiekty, tablice, wiele kodów i zagnieżdżone ścieżki. Nie zakładaj, że `messages[field]` jest tablicą. Nie renderuj komunikatów jako HTML. Znane komunikaty tłumacz bez gubienia limitów; nierozpoznane zachowuj jako tekst.

## Reguły ofert

- Tytuł i opis są wymagane, minimum 10 znaków w dodawaniu i edycji. Maksimum pochodzi z `offer_name_max_length` i `offer_description_max_length`, z domyślnymi wartościami 255 i 2000. Limit atrybutu domyślnie wynosi 500.
- Dodawanie wymaga pojedynczej kategorii i subkategorii oraz niepustych list dodatnich identyfikatorów województw i miejscowości. Wyłączona zależna kontrolka nie oznacza zwolnienia z wymagania.
- Atrybuty waliduj z definicji kategorii, także podczas edycji. Formularz edycji pobiera bieżące definicje i dołącza pola, które nie mają jeszcze zapisanej wartości. Nie opieraj formularza tylko na istniejących wartościach.
- Zdjęcia mają wspólny limit liczby `offer_max_images` (domyślnie 10); metadane nowego obrazu wymagają MIME `image/*`, rozmiaru większego niż zero i nie większego niż 20 MB i nazwy do 255 znaków bez znaków specjalnych innych niż spacja, kropka, myślnik i podkreślenie. Przygotowywanie plików blokuje wysłanie; błędy są wyłącznie przy przycisku wyboru.
- Kolejność sekcji dodawania: kategoria, tytuł i opis, lokalizacja, atrybuty, zdjęcia. Sekcje mają nagłówki i separatory. Atrybuty wymagane są pierwsze, stabilnie względem kolejności słownika; sortowanie widoku nie zmienia powiązania kontrolki z `idAttribute` ani indeksu przesyłanego w żądaniu.
- Atrybuty korzystają z kompaktowych pól: jedna kolumna na telefonie, dwie od szerokości `md`. Nie zmniejszaj obszaru przycisków ani czytelności tekstu. Opis oferty zachowuje większe pole z możliwością pionowego powiększenia.

## Minimalna walidacja zmiany

- Testuj krótką nazwę, granice długości, białe znaki, znaki spoza BMP, obiektowy błąd API, kilka błędów pola, błędy zagnieżdżone, nieznane pola, HTTP 5xx, poprawienie i ponowienie oraz brak podwójnych żądań.
- Sprawdź wymagane i opcjonalne atrybuty, zero, typy liczbowe, opcje, zmianę kategorii i zgodność identyfikatorów po sortowaniu.
- Uruchom testy zmienionych formularzy/API, produkcyjny build Angular, lint PHP i `git diff --check`. Bez publikowania testowych ofert i bez wysyłania wiadomości czy płatności na produkcji.

## Walidatory biblioteki

- `Base\Validator\Nip` i `Base\Validator\Pesel` odrzucają niepoprawny typ, długość i znaki przed sprawdzeniem sumy kontrolnej. Błędne wejście ma zwracać komunikat walidacji, bez ostrzeżeń PHP lub wyjątków.
- Cyfra kontrolna PESEL to `(10 - suma % 10) % 10`, także gdy wynosi zero. Nie dodawaj zależności od BCMath dla tej operacji.
- Test: `php tests/identifiers.php <consumer-vendor-autoload.php>`; ładuje lokalne klasy biblioteki i zależności Laminas konsumenta.
- Po zatwierdzonej publikacji biblioteki odśwież Composer lock aplikacji konsumującej Base. Nie publikuj zmian ani locka bez zgody na konkretną operację.
