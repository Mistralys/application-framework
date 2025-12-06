<?php

declare(strict_types=1);

namespace TestDriver\API\Versioned;

use Application\API\Versioning\BaseAPIVersion;

class Versioned_1_0 extends BaseAPIVersion
{
    public const string VERSION = '1.0';

    public function getVersion(): string
    {
        return self::VERSION;
    }

    public function getDeprecatedKeys(): array
    {
        return array();
    }

    public function getRemovedKeys(): array
    {
        return array();
    }

    protected function _getChangelog(): string
    {
        return <<<'MARKDOWN'
- Initial version.
MARKDOWN;
    }
}
