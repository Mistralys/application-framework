<?php
/**
 * @package DBHelper
 * @subpackage Admin Screens
 */

declare(strict_types=1);

namespace DBHelper\Admin;

use Application\Collection\Admin\BaseRecordSelectionTieIn;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * Tie-in class for selecting a DB record from a short list
 * in an administration screen.
 *
 * For documentation, see the base class {@see BaseRecordSelectionTieIn}.
 * The current class is a DBHelper-specific implementation of that class.
 *
 * @package DBHelper
 * @subpackage Admin Screens
 */
abstract class BaseDBRecordSelectionTieIn
    extends BaseRecordSelectionTieIn
    implements DBRecordSelectionTieInInterface
{
    public function getRecordID(): ?int
    {
        $id = parent::getRecordID();
        if($id !== null) {
            return (int)$id;
        }

        return null;
    }

    protected function recordIDExists($id): bool
    {
        return $this->getCollection()->idExists((int)$id);
    }

    protected function getRecordByID($id): DBHelperRecordInterface
    {
        return $this->getCollection()->getByID((int)$id);
    }

    public function getRequestPrimaryVarName(): string
    {
        return $this->getCollection()->getRecordRequestPrimaryName();
    }

    protected function getEmptySelectionText() : string
    {
        return t('No %1$s records are available.', $this->getCollection()->getRecordLabel());
    }
}
