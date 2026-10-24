<?php

namespace Base\MassAction;

/** Expected, user-facing rejection; infrastructure exceptions must not expose their messages. */
class Rejection extends \DomainException {}
