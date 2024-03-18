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

    public function __construct(ChangelogableInterface $changelogable)
    {
        $this->changelogable = $changelogable;
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
    private static ?array $changelogTypeLabels = null;

    public static function getTypeLabels() : array
    {
        if(isset(self::$changelogTypeLabels)) {
            return self::$changelogTypeLabels;
        }

        $callback = array(static::class, '_getTypeLabels');

        self::$changelogTypeLabels = $callback();

        return self::$changelogTypeLabels;
    }

    /**
     * @return array<string,string> Changelog type => Human-readable label pairs.
     */
    abstract protected function _getTypeLabels() : array;
}
