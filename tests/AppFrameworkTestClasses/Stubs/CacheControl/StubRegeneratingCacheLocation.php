<?php
/**
 * @package Application Tests
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\CacheControl;

use Application\CacheControl\BaseCacheLocation;
use Application\CacheControl\RegeneratingCacheLocationInterface;

/**
 * {@see RegeneratingCacheLocationInterface} stub for testing the
 * {@see \Application\CacheControl\CacheManager} regeneration path.
 *
 * Records whether {@see self::clear()} (which must never be called
 * by the cache manager for this kind of location) or {@see self::regenerate()}
 * were called, and in which relative order, as well as supporting a
 * configurable failure mode to exercise the error handling path.
 *
 * @package Application Tests
 * @subpackage CacheControl
 */
class StubRegeneratingCacheLocation extends BaseCacheLocation implements RegeneratingCacheLocationInterface
{
    private string $id;
    private bool $cleared = false;
    private bool $regenerated = false;
    private ?\Throwable $failure = null;

    /**
     * @var array<int,string> Shared call-order counter array, pushed to by {@see self::clear()} and {@see self::regenerate()}.
     */
    private array $callOrderLog;

    /**
     * @param string $id
     * @param array<int,string> $callOrderLog Reference to a shared log array, calls append `"{$id}:clear"` or `"{$id}:regenerate"`.
     */
    public function __construct(string $id = 'stub-regenerating', array &$callOrderLog = array())
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
        return 'Stub regenerating cache location';
    }

    /**
     * Configures this stub to throw the given throwable when
     * {@see self::regenerate()} is called, instead of succeeding.
     */
    public function failRegenerationWith(\Throwable $e): self
    {
        $this->failure = $e;
        return $this;
    }

    public function clear(): void
    {
        $this->cleared = true;
        $this->callOrderLog[] = $this->id . ':clear';
    }

    public function regenerate(): void
    {
        $this->callOrderLog[] = $this->id . ':regenerate';

        if ($this->failure !== null) {
            throw $this->failure;
        }

        $this->regenerated = true;
    }

    public function wasCleared(): bool
    {
        return $this->cleared;
    }

    public function wasRegenerated(): bool
    {
        return $this->regenerated;
    }

    /**
     * @return array<int,string>
     */
    public function getCallOrderLog(): array
    {
        return $this->callOrderLog;
    }
}
