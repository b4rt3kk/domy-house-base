# Model dictionaries and cache keys

- `Base\Dictionary` is mutable. Clone and initialize it before configuring a model, label fields or ID filter. Named dictionaries such as `yes_no` use their callable and do not require a database model.
- A model dictionary's cache key must render its filtered `Select` with the platform from the model's TableGateway adapter: `getSqlString($platform)`. Calling `getSqlString()` without a platform falls back to SQL92; quoting predicate values then raises a PHP warning. Mezzio's error handler promotes that warning to an exception and returns HTTP 500 before the dictionary query runs, including for complete offers with valid pricing.
- Do not suppress warnings or turn this infrastructure failure into missing-field validation. Preserve the query predicates in the cache key so different requested IDs cannot share cached labels. Named dictionaries retain their existing name-based key.
- `tests/DictionaryTest.php` exercises real dictionary/cache rendering with a PostgreSQL platform, a fake driver and warning-to-exception handling; no database is contacted. This package uses the consuming application's Laminas/PHPUnit dependencies. Run its PHPUnit executable with `--no-configuration --bootstrap <consumer>/vendor/autoload.php <base>/tests/DictionaryTest.php`.
- After publishing an approved Base fix to `origin`, refresh the consumer's `bwiechnik/domy-house-base` Composer lock reference before building its image. Local tests that preload this checkout's class do not update the locked production dependency.

## Configuration contract and cache identity

- `dictionaryName` takes precedence over `modelName`. Named dictionary behavior is
  supplied by the consumer's callable; setting `idKey`, `nameFields` or `where`
  does not guarantee that its callable uses them.
- Model dictionaries accept a registered `AbstractModel` service name, `idKey`
  (default `id`), ordered `nameFields`, `separator` (default one space), and
  Laminas `where` predicates. Their output is an associative value-to-label map;
  duplicate keys overwrite earlier rows. There is no automatic ghost filter or
  explicit ordering in the dictionary's select. Do not treat arbitrary stored
  model names, column names or SQL snippets as a safe administrator-facing DSL.
- `init()`/`reset()` reset the configuration and language (`pl_pl`) but retain
  the named callable and the `useCache` flag. Set caching policy explicitly when
  cloning a configured service.
- Cache identity includes a version, the platform-rendered select or named
  dictionary name, model identity, ID field, ordered label fields, separator and
  language. Earlier keys collided for different projections/languages; changing
  the key version prevents reusing those entries. Disabling this cache alone
  does not disable the underlying model cache.
- Regression coverage must retain different keys and
  labels on the same filtered model, separators, named languages, predicates,
  zero keys, and caller isolation. Keep SQL rendering platform-aware.
