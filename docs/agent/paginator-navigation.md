# Nawigacja paginatora

`paginator_navigation.phtml` renderuje pasek stron, liczniki i wybór rozmiaru
strony. Dolny partial deleguje do tego samego szablonu. Każde renderowanie ma
unikalne identyfikatory etykiet/selectów; top/bottom ani różne listy nie mogą
powielać `items_per_page` jako ID. Nazwa pola pozostaje `items_per_page`:
`paginator.phtml` używa jej w obsłudze AJAX zapisu rozmiaru strony do sesji.

Klasy `base-paginator-*` umożliwiają adminowi MVC osobne style bez zmiany tabel
wyników. Desktop zachowuje linki numerowane. Blok `base-paginator-mobile` jest
domyślnie ukryty (`d-none`), a wyłącznie CSS admina pokazuje go poniżej 768 px.
Zawiera `base-paginator-page-field` (etykieta i select wszystkich stron)
oraz grupę pierwsza/poprzednia/następna/ostatnia. CSS admina umieszcza te dzieci
w nadrzędnej siatce przez `display: contents`: wybór strony i rozmiaru obok siebie,
cztery skróty w jednym rzędzie, wyniki i czas wczytania na dole. Klasy
`base-paginator-position`, `base-paginator-results`, `base-paginator-duration`
oddzielają powtórzony licznik strony od metadanych. Skrócona etykieta
`base-paginator-size-short-label` jest domyślnie ukryta; wyłącznie adminowy CSS
pokazuje „Na stronie” zamiast dłuższego tekstu. Pole zachowuje pełne `aria-label`.
Nie dodawaj do Base stylów wymuszających ten układ u pozostałych odbiorców.
Pusta lista ma jedną stronę prezentacji (1 z 1), a wszystkie skróty są wyłączone.
Licznik i cele linków są ograniczone do istniejącego zakresu.
URL-e muszą pochodzić z helpera routingu; granice mają `aria-disabled` i nie
wchodzą do kolejności tabulacji. Obsługa zmiany strony należy do JS admina.

Test w MVC: `python3 tools/check-admin-mobile-navigation.py --base-view-root
../domy-house-base/src/view`. Obejmuje oba partiale, unikalne ID, granice,
wybór strony, rozmiar strony i geometrię przy otwartym/zamkniętym sidebarze,
puste wyniki, jedną i wiele stron. Skróty muszą zostać w jednym rzędzie także
przy 320 px, a kontrolki mieć równe wysokości i wyrównane etykiety.
Przed wdrożeniem opublikuj Base i odśwież lock MVC zgodnie z AGENTS.md.

Wspólne tabele mają pod nawigacją wybór wyświetlanych kolumn z przyciskami Zastosuj/Domyślne. Kontrakt modelu, sesji i bezpieczeństwa: `paginator-columns.md`. Kontrolki kolumn są poza mobilną siatką strony/rozmiaru, a górny i dolny pasek synchronizują wybór.
