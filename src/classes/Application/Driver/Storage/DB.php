<?php

class Application_Driver_Storage_DB extends Application_Driver_Storage
{
    const string TABLE_NAME = 'app_settings';

    const string COL_VALUE = 'data_value';
    const string COL_KEY = 'data_key';
    const string COL_EXPIRY = 'expiry_date';
    const string COL_ROLE = 'data_role';

    public function get($name)
    {
        $value = DBHelper::createFetchKey(self::COL_VALUE, self::TABLE_NAME)
            ->whereValue(self::COL_KEY, $name)
            ->fetchString();

        if(empty($value)) {
            return null;
        }

        $expiryDate = $this->getExpiry($name);

        if($expiryDate === null || $expiryDate >= new DateTime()) {
            return $value;
        }

        return null;
    }

    public function getExpiry(string $name) : ?DateTime
    {
        // FIXME: Can be removed once this is supported everywhere.
        if(!DBHelper::columnExists(self::TABLE_NAME, self::COL_EXPIRY)) {
            return null;
        }

        $date = DBHelper::createFetchKey(self::COL_EXPIRY, self::TABLE_NAME)
            ->whereValue(self::COL_KEY, $name)
            ->fetchString();

        if(!empty($date)) {
            return DateTime::createFromFormat('Y-m-d H:i:s', $date);
        }

        return null;
    }

    public function set($name, $value, $role)
    {
        DBHelper::insertOrUpdate(
            self::TABLE_NAME,
            array(
                self::COL_KEY => $name,
                self::COL_VALUE => $value,
                self::COL_ROLE => $role
            ),
            array(
                self::COL_KEY
            )
        );
    }

    public function setExpiry(string $name, DateTime $date) : void
    {
        // FIXME: Can be removed once this is supported everywhere.
        if(!DBHelper::columnExists(self::TABLE_NAME, self::COL_EXPIRY)) {
            return;
        }

        DBHelper::updateDynamic(
            self::TABLE_NAME,
            array(
                self::COL_EXPIRY => $date,
                self::COL_KEY => $name
            ),
            array(
                self::COL_KEY
            )
        );
    }

    public function delete($name)
    {
        DBHelper::deleteRecords(
            self::TABLE_NAME,
            array(
                self::COL_KEY => $name
            )
        );
    }
}
