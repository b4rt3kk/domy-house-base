<?php

namespace Base\MassAction;

/** Bounded scalar payload avoids PHP max_input_vars truncating checkbox lists. */
final class Selection
{
    public const LIMIT = 100;

    public static function ids($value): array
    {
        if (!is_string($value) || strlen($value) > self::LIMIT * 20
            || !preg_match('/^[1-9][0-9]*(,[1-9][0-9]*)*$/D', $value)) {
            throw new \InvalidArgumentException('Wybierz co najmniej jeden poprawny rekord.');
        }
        $ids = explode(',', $value);
        if (count($ids) > self::LIMIT) {
            throw new \InvalidArgumentException('Jednorazowo można edytować najwyżej ' . self::LIMIT . ' rekordów.');
        }
        foreach ($ids as $id) {
            if (strlen($id) > strlen((string) PHP_INT_MAX)
                || (strlen($id) === strlen((string) PHP_INT_MAX) && strcmp($id, (string) PHP_INT_MAX) > 0)) {
                throw new \InvalidArgumentException('Niepoprawny identyfikator rekordu.');
            }
        }
        $ids = array_values(array_unique($ids));
        sort($ids, SORT_NUMERIC);
        return $ids;
    }
}
