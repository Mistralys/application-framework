<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\LDAP;

use AppFrameworkTestClasses\ApplicationTestCase;

abstract class LDAPTestCase extends ApplicationTestCase
{
    public const string LDAP_HOST = '127.0.0.1';
    public const ?int LDAP_PORT = 9689;
    public const bool LDAP_SSL_ENABLED = false;
    public const string LDAP_DN = 'dc=mokapi,dc=io';
    public const string LDAP_USERNAME = 'uid=awilliams,dc=mokapi,dc=io';
    public const string LDAP_PASSWORD = 'foo123';
    public const string LDAP_MEMBER_SUFFIX = ',dc=mokapi,dc=io';
}
