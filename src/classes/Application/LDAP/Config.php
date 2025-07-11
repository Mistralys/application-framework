<?php
/**
 * @package LDAP
 * @subpackage Configuration
 */

declare(strict_types=1);

/**
 * Configuration utility for LDAP connections. Used to set
 * connection parameters such as the host, port, and credentials.
 *
 * @package LDAP
 * @subpackage Configuration
 */
class Application_LDAP_Config
{
    public const DEFAULT_PROTOCOL_VERSION = 3;
    public const DEFAULT_PORT_SSL_DISABLED = 389;
    public const DEFAULT_PORT_SSL_ENABLED = 636;

    private string $host;
    private ?int $port;
    private string $password;
    private string $dn;
    private string $username;
    private string $memberSuffix = '';
    private bool $debug = false;
    private int $protocolVersion = self::DEFAULT_PROTOCOL_VERSION;
    private bool $ssl = true;

    public function __construct(string $host, ?int $port, string $dn, string $username, string $password)
    {
        if($port === 0) {
            $port = null;
        }

        $this->host = $this->filterHost($host);
        $this->port = $port;
        $this->dn = $dn;
        $this->username = $username;
        $this->password = $password;
    }

    private function filterHost(string $host): string
    {
        // Remove any trailing slashes or spaces
        $host = trim(rtrim($host, '/ '));

        if(str_contains($host, '://')) {
            // Remove the scheme if present
            $host = substr($host, strpos($host, '://') + 3);
        }

        return $host;
    }

    public function getProtocolVersion(): int
    {
        return $this->protocolVersion;
    }

    public function setProtocolVersion(int $version): self
    {
        $this->protocolVersion = $version;
        return $this;
    }

    public function setDebug(bool $debug) : self
    {
        $this->debug = $debug;
        return $this;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Returns the URI of the LDAP server in the format
     * `ldap://host:port`.
     *
     * @return string
     */
    public function getURI() : string
    {
        $scheme = 'ldap';
        if($this->ssl) {
            $scheme = 'ldaps';
        }

        return sprintf(
            '%s://%s:%d',
            $scheme,
            $this->getHost(),
            $this->getPort()
        );
    }

    /**
     * Whether to use SSL for the connection.
     * @param bool $enabled
     * @return $this
     */
    public function setSSLEnabled(bool $enabled) : self
    {
        $this->ssl = $enabled;
        return $this;
    }

    /**
     * Returns whether SSL is enabled for the connection.
     *
     * @return bool
     */
    public function isSSLEnabled(): bool
    {
        return $this->ssl;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        if(isset($this->port)) {
            return $this->port;
        }

        if($this->ssl) {
            return self::DEFAULT_PORT_SSL_ENABLED;
        }

        return self::DEFAULT_PORT_SSL_DISABLED;
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
