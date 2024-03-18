<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace TestDriver\Revisionables;

use Application\Changelog\BaseChangelogHandler;
use Application_Changelog_FilterCriteria;

/**
 * @package Application
 * @subpackage Revisionables
 */
class ChangelogHandler extends BaseChangelogHandler
{
    public const CHANGELOG_SET_ALIAS = 'set_alias';

    public function configureFilters(Application_Changelog_FilterCriteria $filters): void
    {
    }

    public function getEntryText(string $type, array $data): string
    {
        return '';
    }

    public function getEntryDiff(string $type, array $data = array()): ?array
    {
        return null;
    }

    protected function _getTypeLabels(): array
    {
        return array(
            self::CHANGELOG_SET_ALIAS => t('Set alias')
        );
    }
}
