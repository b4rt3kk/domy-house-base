# Tekst i URL-e użytkownika

- `Base\Validator\Url` dopuszcza wyłącznie HTTP/HTTPS lub domenę bez schematu (gdy `requireScheme=false`). Odrzuca inne schematy, credentials, znaki kontrolne, cudzysłowy, nawiasy HTML i backslash. Kodowanie HTML nie zabezpiecza schematu URL.
- Formatery `FORMAT_TRUNCATE` i `FORMAT_TEXT_TRUNCATE` kodują tekst i atrybuty HTML. Tooltip jest tekstowy (`data-bs-html=false`); nigdy nie przekazuj opisu użytkownika do tooltipa interpretującego HTML. Zachowuj istniejące encje bez podwójnego kodowania, a przy skracaniu dekoduj przed przycięciem i zakoduj wynik.
- Walidacja: `php tests/text-security.php <consumer-vendor-autoload.php>`; test ładuje lokalne klasy Base oraz zależności Laminas konsumenta. Po publikacji Base odśwież lock obu aplikacji PHP przed budowaniem obrazów/legacy.
