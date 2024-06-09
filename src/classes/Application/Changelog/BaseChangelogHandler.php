<?php
/**
 * @package Application
 * @subpackage Changelogable
 */

declare(strict_types=1);

namespace Application\Changelog;

use Application\Interfaces\ChangelogableInterface;
use Application\Interfaces\ChangelogHandlerInterface;
use Application\Traits\ChangelogViaHandlerTrait;

/**
 * Abstract base class that can be extended to implement
 * a changelog handler: It handles preparing human-readable
 * changelog entries and diffs for a changelogable object.
 *
 * @package Application
 * @subpackage Changelogable
 *
 * @see ChangelogViaHandlerTrait
 * @see ChangelogableInterface
 * @see ChangelogHandlerInterface
 */
abstract class BaseChangelogHandler implements ChangelogHandlerInterface
{
    protected ChangelogableInterface $changelogable;

    /**
     * @var array<string,callable>
     */
    protected array $textCallbacks = array();

    public function __construct(ChangelogableInterface $changelogable)
    {
        $this->changelogable = $changelogable;

        $this->registerTextCallbacks();
    }

    abstract protected function registerTextCallbacks() : void;

    protected function registerTextCallback(string $type, callable $callback) : void
    {
        $this->textCallbacks[$type] = $callback;
    }

    public function getChangelogable() : ChangelogableInterface
    {
        return $this->changelogable;
    }

    public function getTypeLabel(string $type): string
    {
        $labels = self::getTypeLabels();

        return $labels[$type] ?? $type;
    }

    /**
     * @var array<string,string>|null
     */
    protected static ?array $changelogTypeLabels = null;

    public static function getTypeLabels() : array
    {
        if(isset(static::$changelogTypeLabels)) {
            return static::$changelogTypeLabels;
        }

        $callback = array(static::class, '_getTypeLabels');

        static::$changelogTypeLabels = $callback();

        return static::$changelogTypeLabels;
    }

    /**
     * @return array<string,string> Changelog type => Human-readable label pairs.
     */
    abstract protected static function _getTypeLabels() : array;

    public function getEntryText(string $type, array $data): string
    {
        if(isset($this->textCallbacks[$type])) {
            return call_user_func($this->textCallbacks[$type], $data);
        }

        return $this->_getEntryText($type, $data);
    }

    protected function _getEntryText(string $type, array $data) : string
    {
        return '';
    }
}
