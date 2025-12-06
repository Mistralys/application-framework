<?php

declare(strict_types=1);

use Application\Disposables\DisposableInterface;
use Application\Disposables\DisposableTrait;
use DBHelper\BaseCollection\DBHelperCollectionInterface;

class DBHelper_BaseCollection_Keys implements DisposableInterface
{
    use Application_Traits_Loggable;
    use Application_Traits_Eventable;
    use DisposableTrait;

    public const int ERROR_KEY_ALREADY_REGISTERED = 71401;

    private DBHelperCollectionInterface $collection;

    /**
     * @var array<string,DBHelper_BaseCollection_Keys_Key>
     */
    private array $keys = array();

    public function __construct(DBHelperCollectionInterface $collection)
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

        $key = new DBHelper_BaseCollection_Keys_Key($name);
        $this->keys[$name] = $key;
        return $key;
    }

    protected function _getIdentification() : string
    {
        return sprintf(
            '%s | DataKeys',
            $this->collection->getIdentification()
        );
    }

    protected function _getIdentificationDisposed(): string
    {
        return 'Collection | DataKeys (Disposed)';
    }

    public function getChildDisposables() : array
    {
        return array();
    }

    protected function _dispose() : void
    {
        $this->keys = array();
    }
}
