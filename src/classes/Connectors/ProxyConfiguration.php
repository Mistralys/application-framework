<?php
/**
 * @package Connectors
 * @subpackage Configuration
 */

declare(strict_types=1);

namespace Connectors;

/**
 * Utility class to hold proxy configuration details.
 *
 * @package Connectors
 * @subpackage Configuration
 */
class ProxyConfiguration
{
    private string $host;
    private int $port;
    private ?string $username;
    private ?string $password;

    public function __construct(string $host, int $port, ?string $username = null, ?string $password = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
