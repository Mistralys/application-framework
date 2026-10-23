<?php
/**
 * @package Application Tests
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\CacheControl;

use Application\CacheControl\BaseCacheLocation;

/**
 * Plain, non-regenerating cache location stub for testing
 * the {@see \Application\CacheControl\CacheManager}.
 *
 * Records whether {@see self::clear()} was called, so tests
 * can assert on clearing order and behavior.
 *
 * @package Application Tests
 * @subpackage CacheControl
 */
class StubCacheLocation extends BaseCacheLocation
{
    private string $id;
    private bool $cleared = false;

    /**
     * @var array<int,string> Shared call-order counter array, pushed to by {@see self::clear()}.
     */
    private array $callOrderLog;

    /**
     * @param string $id
     * @param array<int,string> $callOrderLog Reference to a shared log array; calls append `"{$id}:clear"`.
     */
    public function __construct(string $id = 'stub-plain', array &$callOrderLog = array())
    {
        $this->id = $id;
        $this->callOrderLog = &$callOrderLog;
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getByteSize(): int
    {
        return 0;
    }

    public function getLabel(): string
    {
        return 'Stub plain cache location';
    }

    public function clear(): void
    {
        $this->cleared = true;
        $this->callOrderLog[] = $this->id . ':clear';
    }

    public function wasCleared(): bool
    {
        return $this->cleared;
    }

    /**
     * @return array<int,string>
     */
    public function getCallOrderLog(): array
    {
        return $this->callOrderLog;
    }
}
