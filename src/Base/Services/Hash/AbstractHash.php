<?php

namespace Base\Services\Hash;

abstract class AbstractHash
{
    abstract public function getHash($string);
}
