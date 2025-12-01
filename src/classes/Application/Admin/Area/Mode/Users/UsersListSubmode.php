<?php
/**
 * @package Application
 * @subpackage Administration
 * @see \Application\Admin\Area\Mode\Users\UsersListSubmode
 */

declare(strict_types=1);

namespace Application\Admin\Area\Mode\Users;

use Application\AppFactory;
use Application\AppFactory\AppFactoryException;
use Application\Exception\UnexpectedInstanceException;
use Application\Users\Admin\UserAdminScreenRights;
use Application_Admin_Area_Mode_Submode_CollectionList;
use Application_Admin_Area_Mode_Users;
use Application_Users;
use Application_Users_User;
use DBHelper\Admin\Screens\Submode\BaseRecordListSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_BaseFilterCriteria_Record;

/**
 * Abstract submode for the users list screen in the
 * user management area.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @property Application_Admin_Area_Mode_Users $mode
 */
abstract class UsersListSubmode extends BaseRecordListSubmode
{
    public const string URL_NAME = 'list';

    public const string COL_FIRST_NAME = 'firstname';
    public const string COL_LAST_NAME = 'lastname';
    public const string COL_EMAIL = 'email';
    public const string COL_ID = 'id';

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

    public function getRequiredRight(): string
    {
        return UserAdminScreenRights::SCREEN_LIST;
    }

    /**
     * @return Application_Users
     * @throws AppFactoryException
     */
    protected function createCollection() : Application_Users
    {
        return AppFactory::createUsers();
    }

    protected function getEntryData(DBHelperRecordInterface $record, DBHelper_BaseFilterCriteria_Record $entry) : array
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
