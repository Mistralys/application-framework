<?php

declare(strict_types=1);

namespace Application\Revisionable\Admin\Traits;

use Application\Revisionable\Admin\RequestTypes\RevisionableCollectionScreenInterface;
use Application\Revisionable\Collection\BaseRevisionableDataGridMultiAction;

/**
 *
 * @see RevisionableListScreenTrait
 */
interface RevisionableListScreenInterface extends RevisionableCollectionScreenInterface
{
    public const string URL_NAME = 'list';

    public function getBackOrCancelURL(): string;

    /**
     * @param string $className
     * @param string $label
     * @param string $redirectURL
     * @param boolean $confirm
     * @return BaseRevisionableDataGridMultiAction
     */
    public function addMultiAction(string $className, string $label, string $redirectURL, bool $confirm = false): BaseRevisionableDataGridMultiAction;
}
