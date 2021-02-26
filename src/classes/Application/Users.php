<?php
/**
 * Class containing the {@link Application_Users} class.
 * 
 * @package Application
 * @subpackage Users
 * @see Application_Users
 */

/**
 * User management class: allows retrieving and modifiying the
 * users available in the database. This is not like the 
 * {@link Application_User} class, which only handles the user
 * which is currently logged in. 
 *
 * @package Application
 * @subpackage Users
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_Users_User
 * 
 * @method Application_Users_User|NULL getByKey(string $key, string $value)
 * @method Application_Users_User createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 */
class Application_Users extends DBHelper_BaseCollection
{
    const TABLE_USER_EMAILS = 'user_emails';

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordClassName()
     */
    public function getRecordClassName()
    {
        return 'Application_Users_User';
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordFiltersClassName()
     */
    public function getRecordFiltersClassName()
    {
        return 'Application_Users_FilterCriteria';
    }

    public function getRecordFilterSettingsClassName()
    {
        return 'Application_Users_FilterSettings';
    }
    
    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordDefaultSortKey()
     */
    public function getRecordDefaultSortKey()
    {
        return 'email';
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordSearchableColumns()
     */
    public function getRecordSearchableColumns()
    {
        return array(
            'firstname' => t('First name'),
            'lastname' => t('Last name'),
            'email' => t('E-mail address')
        );
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordTableName()
     */
    public function getRecordTableName()
    {
        return 'known_users';
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordPrimaryName()
     */
    public function getRecordPrimaryName()
    {
        return 'user_id';
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordTypeName()
     */
    public function getRecordTypeName()
    {
        return 'user';        
    }
    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getCollectionLabel()
     */
    public function getCollectionLabel()
    {
        return t('Users');
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordLabel()
     */
    public function getRecordLabel()
    {
        return t('User');
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordProperties()
     */
    public function getRecordProperties()
    {
        return array();
    }

    public function getByEmail(string $email) : ?Application_Users_User
    {
        $user = $this->getByKey('email', $email);

        if($user !== null)
        {
            return $user;
        }

        // TODO Remove this once the table has been created everywhere.
        if(DBHelper::tableExists(self::TABLE_USER_EMAILS))
        {
            $id = DBHelper::createFetchKey('user_id', self::TABLE_USER_EMAILS)
                ->whereValue('email', $email)
                ->fetchInt();

            if ($id > 0)
            {
                return $this->getByID($id);
            }
        }

        return null;
    }

    public function createNewUser(string $email, string $firstname, string $lastname, string $foreignID='') : Application_Users_User
    {
        return $this->createNewRecord(array(
            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'foreign_id' => $foreignID
        ));
    }

    /**
     * @param int $record_id
     * @return Application_Users_User
     * @throws Application_Exception
     */
    public function getByID(int $record_id)
    {
        $user = parent::getByID($record_id);

        if($user instanceof Application_Users_User)
        {
            return $user;
        }

        throw new Application_Exception_UnexpectedInstanceType(Application_Users_User::class, $user);
    }

    public function initSystemUsers() : void
    {
        $ids = Application::getSystemUserIDs();

        foreach($ids as $id)
        {
            $user = Application::createUser($id);
            $this->initSystemUser($user);
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
                    'email' => $user->getEmail(),
                    'firstname' => $user->getFirstname(),
                    'lastname' => $user->getLastname(),
                    'foreign_id' => $user->getForeignID()
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
}
