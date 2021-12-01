<?php

namespace Base\Services\Hash;

class Md5 extends AbstractHash
{
    public function getHash($string)
    {
        return md5($string);
    }
}
