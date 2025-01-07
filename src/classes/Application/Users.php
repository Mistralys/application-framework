<?php
/**
 * @package Application
 * @subpackage Users
 */

use Application\Exception\DisposableDisposedException;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Users\UsersFilterCriteria;
use Application\Users\UsersFilterSettings;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;

/**
 * User management class: allows retrieving and modifying the
 * users available in the database. This is not like the 
 * {@link Application_User} class, which only handles the user
 * that is currently logged in.
 *
 * @package Application
 * @subpackage Users
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_Users_User
 * 
 * @method Application_Users_User|NULL getByKey(string $key, string $value)
 * @method Application_Users_User createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 * @method Application_Users_User[] getAll()
 */
class Application_Users extends DBHelper_BaseCollection
{
    public const TABLE_NAME = 'known_users';
    public const TABLE_USER_SETTINGS = Application_User_Storage_DB::TABLE_NAME;

    public const PRIMARY_NAME = 'user_id';
    public const COL_EMAIL = 'email';
    public const COL_FIRSTNAME = 'firstname';
    public const COL_LASTNAME = 'lastname';
    public const COL_FOREIGN_ID = 'foreign_id';

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordClassName()
     */
    public function getRecordClassName() : string
    {
        return Application_Users_User::class;
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordFiltersClassName()
     */
    public function getRecordFiltersClassName() : string
    {
        return UsersFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName() : string
    {
        return UsersFilterSettings::class;
    }
    
    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordDefaultSortKey()
     */
    public function getRecordDefaultSortKey() : string
    {
        return self::COL_EMAIL;
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordSearchableColumns()
     */
    public function getRecordSearchableColumns() : array
    {
        return array(
            self::COL_FIRSTNAME => t('First name'),
            self::COL_LASTNAME => t('Last name'),
            self::COL_EMAIL => t('E-mail address')
        );
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordTableName()
     */
    public function getRecordTableName() : string
    {
        return self::TABLE_NAME;
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordPrimaryName()
     */
    public function getRecordPrimaryName() : string
    {
        return self::PRIMARY_NAME;
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordTypeName()
     */
    public function getRecordTypeName() : string
    {
        return 'user';        
    }
    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getCollectionLabel()
     */
    public function getCollectionLabel() : string
    {
        return t('Users');
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordLabel()
     */
    public function getRecordLabel() : string
    {
        return t('User');
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordProperties()
     */
    public function getRecordProperties() : array
    {
        return array();
    }

    public function getByEmail(string $email) : ?Application_Users_User
    {
        $user = $this->getByKey(self::COL_EMAIL, $email);

        if($user !== null)
        {
            return $user;
        }

        return null;
    }

    public function createNewUser(string $email, string $firstname, string $lastname, string $foreignID='') : Application_Users_User
    {
        return $this->createNewRecord(array(
            self::COL_EMAIL => $email,
            self::COL_FIRSTNAME => $firstname,
            self::COL_LASTNAME => $lastname,
            self::COL_FOREIGN_ID => $foreignID
        ));
    }

    /**
     * @param int|string $record_id
     * @return Application_Users_User
     *
     * @throws DisposableDisposedException
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws DBHelper_Exception
     */
    public function getByID($record_id) : DBHelper_BaseRecord
    {
        return ClassHelper::requireObjectInstanceOf(
            Application_Users_User::class,
            parent::getByID($record_id)
        );
    }

    public function getSystemUser() : Application_Users_User
    {
        return $this->getByID(Application::USER_ID_SYSTEM);
    }

    public function initSystemUsers() : void
    {
        $ids = Application::getSystemUserIDs();

        foreach($ids as $id)
        {
            $this->log(sprintf('User [%s] | Processing', $id));

            // This works because the `createUser()` method does not query
            // the database for the system users: It uses hardwired data.
            $this->initSystemUser(Application::createUser($id));
        }
    }

    /**
     * Creates a system user entry in the database using the data
     * from the according auth user object, which is created dynamically.
     *
     * @param Application_User $user
     * @throws Application_Exception
     * @throws DBHelper_Exception
     *
     * @see Application::createUser()
     * @see Application::createSystemUser()
     * @see Application::createDummyUser()
     */
    private function initSystemUser(Application_User $user) : void
    {
        $userID = $user->getID();

        DBHelper::requireTransaction('Initialize system user records');

        if(!$this->idExists($userID))
        {
            $this->log(sprintf('User [%s] | Does not exist, inserting into the database.', $userID));

            // Inserting it manually, since the createNewUser method
            // does not allow specifying an ID.
            DBHelper::insertDynamic(
                $this->getRecordTableName(),
                array(
                    $this->getRecordPrimaryName() => $userID,
                    self::COL_EMAIL => $user->getEmail(),
                    self::COL_FIRSTNAME => $user->getFirstname(),
                    self::COL_LASTNAME => $user->getLastname(),
                    self::COL_FOREIGN_ID => $user->getForeignID()
                )
            );

            return;
        }

        $this->log(sprintf('User [%s] | Exists, updating the record.', $userID));

        $appUser = $this->getByID($userID);

        $appUser->setFirstName($user->getFirstname());
        $appUser->setLastName($user->getLastname());
        $appUser->setForeignID($user->getForeignID());
        $appUser->setEmail($user->getEmail());

        if(!$appUser->save())
        {
            $this->log(sprintf('User [%s] | No changes necessary.', $userID));
        }
    }

    /**
     * @param array<string,string|number> $params
     * @return string
     */
    public function getAdminURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_PAGE] = Application_Admin_Area_Devel::URL_NAME;
        $params[AdminScreenInterface::REQUEST_PARAM_MODE] = Application_Admin_Area_Mode_Users::URL_NAME;

        return Application_Driver::getInstance()
            ->getRequest()
            ->buildURL($params);
    }
}
