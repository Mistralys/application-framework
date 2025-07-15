<?php
/**
 * @package LDAP
 * @subpackage Configuration
 */

declare(strict_types=1);

use Application\LDAP\LDAPException;
use function AppUtils\parseURL;

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
        if(!str_contains($host, '://')) {
            // If the host does not contain a scheme, we assume it's a plain host.
            // We will add the scheme later based on whether SSL is enabled or not.
            $host = 'ldaps://' . $host;
        }

        $info = parseURL($host);

        if(!$info->isValid()) {
            throw new LDAPException(
                'Invalid LDAP host',
                sprintf(
                    'The provided LDAP host [%s] is not valid. '.PHP_EOL.
                    'Validation message: '.PHP_EOL.
                    '%s',
                    $host,
                    $info->getErrorMessage()
                ),
                LDAPException::ERROR_INVALID_HOST
            );
        }

        if($info->getScheme() === 'ldap') {
            $this->setSSLEnabled(false);
        }

        return $info->getHost();
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
        return sprintf(
            '%s:%d',
            $this->getHostURI(),
            $this->getPort()
        );
    }

    /**
     * Returns the URI of the LDAP server, without the port, in the format
     * `ldap://host`.
     *
     * @return string
     */
    public function getHostURI() : string
    {
        $scheme = 'ldap';
        if($this->ssl) {
            $scheme = 'ldaps';
        }

        return sprintf(
            '%s://%s',
            $scheme,
            $this->getHost()
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
