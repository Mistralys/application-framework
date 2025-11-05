<?php
/**
 * @package Application
 * @subpackage Users
 */

use Application\Exception\DisposableDisposedException;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Users\Admin\UsersAdminURLs;
use Application\Users\UserSelector;
use Application\Users\UsersException;
use Application\Users\UsersFilterCriteria;
use Application\Users\UsersFilterSettings;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ConvertHelper\JSONConverter;

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
    public const string TABLE_NAME = 'known_users';
    public const string TABLE_USER_SETTINGS = Application_User_Storage_DB::TABLE_NAME;

    public const string PRIMARY_NAME = 'user_id';
    public const string COL_EMAIL = 'email';
    public const string COL_EMAIL_MD5 = 'email_md5';
    public const string COL_FIRSTNAME = 'firstname';
    public const string COL_LASTNAME = 'lastname';
    public const string COL_FOREIGN_ID = 'foreign_id';
    public const string COL_FOREIGN_NICKNAME = 'foreign_nickname';
    public const string COL_NICKNAME = 'nickname';
    public const string COL_DATE_REGISTERED = 'date_registered';

    public const int COL_FOREIGN_ID_MAX_LENGTH = 250;
    public const int COL_FOREIGN_NICKNAME_MAX_LENGTH = 180;
    public const int COL_NICKNAME_MAX_LENGTH = 180;

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

    public function createNewUser(string $email, string $firstname, string $lastname, string $foreignID='', ?int $userID=null) : Application_Users_User
    {
        $options = array();

        if($userID !== null)
        {
            $options[DBHelper_BaseCollection::OPTION_CUSTOM_RECORD_ID] = $userID;
        }

        return $this->createNewRecord(
            array(
                self::COL_EMAIL => $email,
                self::COL_FIRSTNAME => $firstname,
                self::COL_LASTNAME => $lastname,
                self::COL_FOREIGN_ID => $foreignID
            ),
            false,
            $options
        );
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

            $this->createNewUser(
                $user->getEmail(),
                $user->getFirstname(),
                $user->getLastname(),
                $user->getForeignID(),
                $userID
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

    private ?UsersAdminURLs $usersAdminURLs = null;

    public function adminURL() : UsersAdminURLs
    {
        if(!isset($this->usersAdminURLs)) {
            $this->usersAdminURLs = new UsersAdminURLs();
        }

        return $this->usersAdminURLs;
    }

    public function createUserSelector(Application_Interfaces_Formable $formable) : UserSelector
    {
        return new UserSelector($formable);
    }

    protected function _registerKeys(): void
    {
        $this->keys->register(self::COL_FIRSTNAME)
            ->makeRequired();

        $this->keys->register(self::COL_LASTNAME)
            ->makeRequired();

        $this->keys->register(self::COL_FOREIGN_ID)
            ->makeRequired();

        $this->keys->register(self::COL_EMAIL)
            ->makeRequired();

        $this->keys->register(self::COL_EMAIL_MD5)
            ->makeRequired()
            ->setGenerator($this->generateEmailHash(...));
    }

    private function generateEmailHash(DBHelper_BaseCollection_Keys_Key $key, array $data): string
    {
        $email = $data[self::COL_EMAIL] ?? '';

        if(empty($email) || !is_string($email)) {
            throw new UsersException(
                'Missing email address.',
                sprintf(
                    'Cannot generate MD5 hash for user without e-mail address. '.PHP_EOL.
                    'Provided data set: '.PHP_EOL.
                    '%s',
                    JSONConverter::var2json($data, JSON_PRETTY_PRINT)
                ),
                UsersException::ERROR_MISSING_EMAIL_FOR_HASH
            );
        }

        return self::email2hash($email);
    }

    public static function email2hash(string $email) : string
    {
        return md5(mb_strtolower(trim($email)));
    }
}
