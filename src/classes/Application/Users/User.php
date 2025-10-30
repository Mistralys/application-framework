<?php

declare(strict_types=1);

use Application\Users\Admin\UserAdminScreenRights;
use Application\Users\Admin\UserAdminURLs;
use AppLocalize\Localization\Locales\LocaleInterface;
use AppUtils\Microtime;

class Application_Users_User extends DBHelper_BaseRecord
{
    public const string COL_EMAIL = Application_Users::COL_EMAIL;
    public const string COL_FIRST_NAME = Application_Users::COL_FIRSTNAME;
    public const string COL_LAST_NAME = Application_Users::COL_LASTNAME;

    public function getUserInstance() : Application_User
    {
        return Application::createUser($this->getID());
    }

    public function getEmailMD5() : string
    {
        return $this->getRecordStringKey(Application_Users::COL_EMAIL_MD5);
    }

    public function getUILocale() : LocaleInterface
    {
        return $this->getUserInstance()->getUILocale();
    }

    public function getDateRegistered() : Microtime
    {
        return Microtime::createFromDate($this->getRecordDateKey(Application_Users::COL_DATE_REGISTERED));
    }

    public function getForeignID() : string
    {
        return $this->getRecordStringKey(Application_Users::COL_FOREIGN_ID);
    }

    public function updateEmailAddressHash() : bool
    {
        $current = $this->getEmailMD5();
        $expected = Application_Users::email2hash($this->getEmail());

        if($current === $expected) {
            return false;
        }

        $this->setRecordKey(
            Application_Users::COL_EMAIL_MD5,
            Application_Users::email2hash($this->getEmail())
        );

        $this->save();

        return true;
    }

    protected function init() : void
    {
        $this->registerRecordKey(Application_Users::COL_EMAIL, t('Email Address'), true);
    }

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
        // Automatically update the email MD5 hash when the email address changes
        if($name === Application_Users::COL_EMAIL) {
            $this->setRecordKey(Application_Users::COL_EMAIL_MD5, Application_Users::email2hash((string)$newValue));
        }
    }

    public function getLabel() : string
    {
        $nickname = $this->getNickname();

        if(!empty($nickname)) {
            return $nickname;
        }

        return $this->getName();
    }

    public function getLabelLinked() : string
    {
        return (string)sb()->linkRight(
            $this->getLabel(),
            $this->adminURL()->status(),
            UserAdminScreenRights::SCREEN_VIEW_STATUS
        );
    }
    
    public function getName() : string
    {
        return $this->getFirstname().' '.$this->getLastname();
    }
    
    public function getFirstname() : string
    {
        return $this->getRecordStringKey(self::COL_FIRST_NAME);
    }
    
    public function getLastname() : string
    {
        return $this->getRecordStringKey(self::COL_LAST_NAME);
    }
    
    public function getEmail() : string
    {
        return $this->getRecordStringKey(self::COL_EMAIL);
    }

    public function setEmail(string $email) : bool
    {
        return $this->setRecordKey(self::COL_EMAIL, $email);
    }

    public function setFirstName(string $firstName) : bool
    {
        return $this->setRecordKey(self::COL_FIRST_NAME, $firstName);
    }

    public function setLastName(string $lastName) : bool
    {
        return $this->setRecordKey(self::COL_LAST_NAME, $lastName);
    }

    public function setForeignID(string $foreignID) : bool
    {
        return $this->setRecordKey(Application_Users::COL_FOREIGN_ID, $foreignID);
    }

    public function getNickname() : string
    {
        return $this->getRecordStringKey(Application_Users::COL_NICKNAME);
    }

    public function getForeignNickname() : string
    {
        return $this->getRecordStringKey(Application_Users::COL_FOREIGN_NICKNAME);
    }

    private ?UserAdminURLs $adminURLs = null;

    public function adminURL() : UserAdminURLs
    {
        if(!isset($this->adminURLs))
        {
            $this->adminURLs = new UserAdminURLs($this);
        }

        return $this->adminURLs;
    }
}
