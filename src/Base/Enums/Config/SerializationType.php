<?php

namespace Base\Enums\Config;

enum SerializationType : string
{
    case Camelcase = "camelcase";
    case Underscore = "underscore";
}
