<?php

declare(strict_types=1);

namespace Application\LDAP;

use Application_Exception;

class LDAPException extends Application_Exception
{
    public const int ERROR_INVALID_HOST = 178801;
}
