<?php
/**
 * @package Application
 * @subpackage Administration
 * @see \Application\Admin\Area\Mode\Users\UsersListSubmode
 */

declare(strict_types=1);

namespace Application\Admin\Area\Mode\Users;

use Application\Exception\UnexpectedInstanceException;
use Application_Admin_Area_Mode_Submode_CollectionList;
use Application_Admin_Area_Mode_Users;
use Application_Driver;
use Application_Users;
use Application_Users_User;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use DBHelper_BaseCollection;
use DBHelper_BaseFilterCriteria_Record;
use DBHelper_BaseRecord;

/**
 * Abstract submode for the users list screen in the
 * user management area.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class UsersListSubmode extends Application_Admin_Area_Mode_Submode_CollectionList
{
    public const URL_NAME = 'list';

    public const COL_FIRST_NAME = 'firstname';
    public const COL_LAST_NAME = 'lastname';
    public const COL_EMAIL = 'email';
    public const COL_ID = 'id';

    /**
     * @var Application_Admin_Area_Mode_Users
     */
    protected $mode;

    public function getNavigationTitle() : string
    {
        return t('List');
    }

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getTitle() : string
    {
        return t('Users list');
    }

    /**
     * @return Application_Users
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    protected function createCollection() : DBHelper_BaseCollection
    {
        return Application_Driver::createUsers();
    }

    protected function getEntryData(DBHelper_BaseRecord $record, DBHelper_BaseFilterCriteria_Record $entry) : array
    {
        if ($record instanceof Application_Users_User)
        {
            return array(
                self::COL_ID => $record->getID(),
                self::COL_FIRST_NAME => $record->getFirstname(),
                self::COL_LAST_NAME => $record->getLastname(),
                self::COL_EMAIL => $record->getEmail()
            );
        }

        throw new UnexpectedInstanceException(Application_Users_User::class, $record);
    }

    protected function configureColumns() : void
    {
        $this->grid->addColumn(self::COL_ID, t('ID'))
            ->setCompact()
            ->alignRight();

        $this->grid->addColumn(self::COL_EMAIL, t('Email'));
        $this->grid->addColumn(self::COL_FIRST_NAME, t('Firstname'));
        $this->grid->addColumn(self::COL_LAST_NAME, t('Lastname'));
    }

    protected function configureActions() : void
    {
    }

    public function getBackOrCancelURL() : string
    {
        return $this->createCollection()->getAdminURL();
    }
}
