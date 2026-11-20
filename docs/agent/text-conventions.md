# Zapis myślników

- W tekstach interfejsu, placeholderach, komunikatach, dokumentacji i nowych danych używaj zwykłego znaku `-` (U+002D), zamiast długiej pauzy U+2014. Dotyczy to także pustej opcji VAT oraz tekstów generowanych przez kod.
- Sprawdzaj również encje HTML i sekwencje escape reprezentujące U+2014; nie wprowadzaj nimi długiej pauzy ponownie.
- Historyczne migracje i ich zasoby są niezmienne: nie poprawiaj w nich typografii po publikacji, ponieważ zmiana sum kontrolnych blokuje migracje/rollback. Istniejące dane poprawiaj osobną, odwracalną operacją, po sprawdzeniu schematu i formatu kolumn.
- Korekta tekstu nie może uszkodzić JSON, serializacji PHP, podpisów ani technicznych identyfikatorów. Kopie odzyskiwania zachowują oryginały; nie normalizuj ich jak bieżących danych aplikacji.
