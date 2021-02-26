<?php

declare(strict_types=1);

class Application_LDAP_Config
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $dn;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $memberSuffix = '';

    public function __construct(string $host, int $port, string $dn, string $username, string $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->dn = $dn;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getDn(): string
    {
        return $this->dn;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $memberSuffix
     * @return $this
     */
    public function setMemberSuffix(string $memberSuffix): Application_LDAP_Config
    {
        $this->memberSuffix = $memberSuffix;
        return $this;
    }

    /**
     * @return string
     */
    public function getMemberSuffix(): string
    {
        return $this->memberSuffix;
    }
}
