<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\LDAP;

use AppFrameworkTestClasses\ApplicationTestCase;

abstract class LDAPTestCase extends ApplicationTestCase
{
    public const LDAP_HOST = '127.0.0.1';
    public const LDAP_PORT = 9689;
    public const LDAP_SSL_ENABLED = false;
    public const LDAP_DN = 'dc=mokapi,dc=io';
    public const LDAP_USERNAME = 'uid=awilliams,dc=mokapi,dc=io';
    public const LDAP_PASSWORD = 'foo123';
    public const LDAP_MEMBER_SUFFIX = ',dc=mokapi,dc=io';
}
