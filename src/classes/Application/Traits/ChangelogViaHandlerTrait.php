<?php
/**
 * @package Application
 * @subpackage Changelogable
 */

declare(strict_types=1);

namespace Application\Traits;

use Application\Changelog\BaseChangelogHandler;
use Application\Interfaces\ChangelogableInterface;
use Application_Changelog_FilterCriteria;
use AppUtils\ClassHelper;

/**
 * Trait that can be used to implement changelog handling
 * using a separate handler class that extends {@see BaseChangelogHandler}.
 *
 * @package Application
 * @subpackage Changelogable
 *
 * @see ChangelogViaHandlerInterface
 * @see ChangelogableInterface
 */
trait ChangelogViaHandlerTrait
{
    private ?BaseChangelogHandler $changelogHandler = null;

    public function getChangelogHandler() : BaseChangelogHandler
    {
        if(!isset($this->changelogHandler)) {
            $class = $this->getChangelogHandlerClass();
            $this->changelogHandler = ClassHelper::requireObjectInstanceOf(
                BaseChangelogHandler::class,
                new $class($this)
            );
        }

        return $this->changelogHandler;
    }

    public function configureChangelogFilters(Application_Changelog_FilterCriteria $filters): void
    {
        $this->getChangelogHandler()->configureFilters($filters);
    }

    public function getChangelogEntryText(string $type, array $data = array()): string
    {
        return $this->getChangelogHandler()->getEntryText($type, $data);
    }

    public function getChangelogEntryDiff(string $type, array $data = array()): ?array
    {
        return $this->getChangelogHandler()->getEntryDiff($type, $data);
    }

    public function getChangelogTypeLabel(string $type): string
    {
        return $this->getChangelogHandler()->getTypeLabel($type);
    }
}
