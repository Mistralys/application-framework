<?php
/**
 * @package Connectors
 * @subpackage Headers
 */

declare(strict_types=1);

namespace Connectors\Headers;

use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Holds information about a single HTTP header.
 *
 * @package Connectors
 * @subpackage Headers
 */
class HTTPHeader implements StringPrimaryRecordInterface
{
    private string $name;
    private string $value;

    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getID(): string
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}