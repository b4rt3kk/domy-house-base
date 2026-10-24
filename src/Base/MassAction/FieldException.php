<?php

namespace Base\MassAction;

class FieldException extends Rejection
{
    public function __construct(public string $field, string $message)
    {
        parent::__construct($message);
    }
}
