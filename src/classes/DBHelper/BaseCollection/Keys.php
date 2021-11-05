<?php

declare(strict_types=1);

class DBHelper_BaseCollection_Keys implements Application_Interfaces_Disposable
{
    use Application_Traits_Loggable;
    use Application_Traits_Eventable;
    use Application_Traits_Disposable;

    public const ERROR_KEY_ALREADY_REGISTERED = 71401;

    /**
     * @var DBHelper_BaseCollection
     */
    private $collection;

    /**
     * @var array<string,DBHelper_BaseCollection_Keys_Key>
     */
    private $keys = array();

    public function __construct(DBHelper_BaseCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return DBHelper_BaseCollection_Keys_Key[]
     */
    public function getAll() : array
    {
        return array_values($this->keys);
    }

    /**
     * @return DBHelper_BaseCollection_Keys_Key[]
     */
    public function getRequired() : array
    {
        $result = array();

        foreach($this->keys as $key)
        {
            if($key->isRequired())
            {
                $result[] = $key;
            }
        }

        return $result;
    }

    /**
     * @param string $name
     * @return DBHelper_BaseCollection_Keys_Key
     * @throws DBHelper_Exception
     */
    public function register(string $name) : DBHelper_BaseCollection_Keys_Key
    {
        if(isset($this->keys[$name]))
        {
            throw new DBHelper_Exception(
                'Key has already been registered.',
                sprintf(
                    'The key [%s] has already been registered, and cannot be registered again.',
                    $name
                ),
                self::ERROR_KEY_ALREADY_REGISTERED
            );
        }

        $key = new DBHelper_BaseCollection_Keys_Key($this, $name);
        $this->keys[$name] = $key;
        return $key;
    }

    public function getIdentification() : string
    {
        return sprintf(
            '%s | DataKeys',
            $this->collection->getIdentification()
        );
    }

    public function getChildDisposables() : array
    {
        return array();
    }

    protected function _dispose() : void
    {
        $this->keys = array();
    }

    public function getLogIdentifier() : string
    {
        return $this->getIdentification();
    }
}
