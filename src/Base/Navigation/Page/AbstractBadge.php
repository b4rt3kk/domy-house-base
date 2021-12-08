<?php

namespace Base\Navigation\Page;

/**
 * Klasa dołączana jest do strony Page\Mvc i wyśwletla ewentualne komunikaty dla tej strony, jak przykładowo liczba nowych wiadomości
 * czy też oznacza akcje wymagające uwagi czy odpowiedzi
 */
abstract class AbstractBadge extends \Base\Logic\AbstractLogic
{
    abstract public function render();
}


