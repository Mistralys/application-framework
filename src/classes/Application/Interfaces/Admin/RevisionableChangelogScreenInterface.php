<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\Interfaces\Admin;

use Application\Traits\Admin\RevisionableChangelogScreenTrait;

/**
 * @package Application
 * @subpackage Revisionables
 *
 * @see RevisionableChangelogScreenTrait
 */
interface RevisionableChangelogScreenInterface extends AdminScreenInterface
{
    public const REVISIONABLE_CHANGELOG_ERROR_NOT_A_VALID_REVISIONABLE = 630001;
    public const COL_TYPE = 'type';
    public const COL_DATE = 'date';
    public const COL_AUTHOR = 'author';
    public const COL_DETAILS = 'details';
    public const COL_ACTIONS = 'actions';
    public const COL_REVISION = 'revision';
    public const COL_CHANGELOG_ID = 'changelog_id';
    public const FILTER_SEARCH = 'search';
    public const FILTER_TYPE = 'type';
    public const FILTER_AUTHOR = 'author';
    public const REQUEST_PARAM_RESET = 'reset';
    public const FILTER_REVISION = 'revision';
    public const FILTER_FROM_DATE = 'from_date';
    public const FILTER_TO_DATE = 'to_date';
}
