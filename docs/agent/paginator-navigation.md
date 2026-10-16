# Nawigacja paginatora

`paginator_navigation.phtml` renderuje pasek stron, liczniki i wybór rozmiaru
strony. Dolny partial deleguje do tego samego szablonu. Każde renderowanie ma
unikalne identyfikatory etykiet/selectów; top/bottom ani różne listy nie mogą
powielać `items_per_page` jako ID. Nazwa pola pozostaje `items_per_page`:
`paginator.phtml` używa jej w obsłudze AJAX zapisu rozmiaru strony do sesji.

Klasy `base-paginator-*` umożliwiają adminowi MVC osobne style bez zmiany tabel
wyników. Desktop zachowuje linki numerowane. Blok `base-paginator-mobile` jest
domyślnie ukryty (`d-none`), a wyłącznie CSS admina pokazuje go poniżej 768 px.
Zawiera select wszystkich stron i pierwszą/poprzednią/następną/ostatnią stronę.
URL-e muszą pochodzić z helpera routingu; granice mają `aria-disabled` i nie
wchodzą do kolejności tabulacji. Obsługa zmiany strony należy do JS admina.

Test w MVC: `python3 tools/check-admin-mobile-navigation.py --base-view-root
../domy-house-base/src/view`. Obejmuje oba partiale, unikalne ID, granice,
wybór strony, rozmiar strony i geometrię przy otwartym/zamkniętym sidebarze.
Przed wdrożeniem opublikuj Base i odśwież lock MVC zgodnie z AGENTS.md.
