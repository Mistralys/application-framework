<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\Revisionable\Admin\Traits;

use Application\Revisionable\Admin\RequestTypes\RevisionableScreenInterface;

/**
 * @package Application
 * @subpackage Revisionables
 *
 * @see RevisionableChangelogScreenTrait
 */
interface RevisionableChangelogScreenInterface extends RevisionableScreenInterface
{
    public const string COL_TYPE = 'type';
    public const string COL_DATE = 'date';
    public const string COL_AUTHOR = 'author';
    public const string COL_DETAILS = 'details';
    public const string COL_ACTIONS = 'actions';
    public const string COL_REVISION = 'revision';
    public const string COL_CHANGELOG_ID = 'changelog_id';
    public const string FILTER_SEARCH = 'search';
    public const string FILTER_TYPE = 'type';
    public const string FILTER_AUTHOR = 'author';
    public const string REQUEST_PARAM_RESET = 'reset';
    public const string FILTER_REVISION = 'revision';
    public const string FILTER_FROM_DATE = 'from_date';
    public const string FILTER_TO_DATE = 'to_date';
}
