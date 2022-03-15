<?php
/**
 * File containing the class {@see \Application\Driver\DriverSettings}.
 *
 * @package Application
 * @subpackage Driver
 * @see \Application\Driver\DriverSettings
 */

declare(strict_types=1);

namespace Application\Driver;

use Application_Driver;
use Application_Driver_Storage;
use Application_Exception;
use AppUtils\ConvertHelper;
use DateTime;
use JsonException;

/**
 * Driver settings utility, used to access and
 * modify the global, persistent application settings.
 *
 * @package Application
 * @subpackage Driver
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DriverSettings
{
    private Application_Driver_Storage $storage;

    public function __construct(Application_Driver_Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Retrieves a persistent application setting by its name.
     *
     * @param string $name
     * @param string|NULL $default
     * @return string|NULL
     */
    public function get(string $name, ?string $default = null) : ?string
    {
        $value = $this->storage->get($name);

        if ($value !== null)
        {
            return $value;
        }

        return $default;
    }

    /**
     * @param string $name
     * @param bool $default
     * @return bool
     */
    public function getBool(string $name, bool $default=false) : bool
    {
        if($this->get($name) === 'true')
        {
            return true;
        }

        return $default;
    }

    public function getInt(string $name, int $default=0) : int
    {
        $value = $this->get($name);

        if($value !== null)
        {
            return (int)$value;
        }

        return $default;
    }

    /**
     * @param string $name
     * @return array<string|int,mixed>
     * @throws JsonException
     */
    public function getArray(string $name) : array
    {
        $value = $this->get($name);

        if($value !== null)
        {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        return array();
    }

    /**
     * Sets an application setting. Can optionally set the role: default
     * is adding a persistent setting, can be set as a cache setting
     * (which may occasionally be deleted).
     *
     * Note: only strings allowed, so serialize arrays and objects
     * beforehand as needed.
     *
     * @param string $name
     * @param string|int|float|bool|NULL $value
     * @param string $role
     * @return $this
     * @throws Application_Exception
     */
    public function set(string $name, $value, string $role = Application_Driver::SETTING_ROLE_PERSISTENT) : self
    {
        $this->requireValidName($name);

        // Empty will work for everything, but will also catch a zero.
        if(empty($value) && $value !== 0)
        {
            return $this->delete($name);
        }

        if(is_bool($value))
        {
            $value = ConvertHelper::boolStrict2string($value);
        }

        $this->storage->set($name, (string)$value, $role);

        return $this;
    }

    public function setInt(string $name, int $value) : self
    {
        return $this->set($name, $value);
    }

    /**
     * Assumes that the setting is used as an integer-based
     * counter. Increases the value by 1, and returns the new
     * value.
     *
     * If the counter does not exist yet, it will start at `1`.
     *
     * @param string $name
     * @return int
     */
    public function increaseCounter(string $name) : int
    {
        $counter = $this->getInt($name) + 1;
        $this->setInt($name, $counter);

        return $counter;
    }

    /**
     * Sets a boolean value.
     *
     * @param string $name
     * @param bool $value
     * @return $this
     * @throws Application_Exception
     */
    public function setBool(string $name, bool $value) : self
    {
        return $this->set($name, ConvertHelper::boolStrict2string($value));
    }

    /**
     * @param string $name
     * @param array<string|int,mixed> $value
     * @return $this
     * @throws Application_Exception
     * @throws JsonException
     */
    public function setArray(string $name, array $value) : self
    {
        return $this->set($name, json_encode($value, JSON_THROW_ON_ERROR));
    }

    /**
     * @param string $name
     * @param DateTime $date
     * @return $this
     */
    public function setExpiry(string $name, DateTime $date) : self
    {
        $this->storage->setExpiry($name, $date);

        return $this;
    }

    /**
     * Deletes an application setting by its name. Has no
     * effect if the setting has already been deleted.
     *
     * @param string $name
     * @return $this
     * @throws Application_Exception
     */
    public function delete(string $name) : self
    {
        $this->requireValidName($name);

        $this->storage->delete($name);

        return $this;
    }

    private function requireValidName(string $name) : void
    {
        if (strlen($name) <= Application_Driver::SETTING_NAME_MAX_LENGTH)
        {
            return;
        }

        throw new Application_Exception(
            'Setting name too long',
            sprintf(
                'Tried setting the setting %1$s, but the name exceeds the maximum %2$s characters.',
                $name,
                Application_Driver::SETTING_NAME_MAX_LENGTH
            )
        );
    }

    public function exists(string $name) : bool
    {
        $value = $this->get($name);

        // Empty works for all empty values, except a '0' as string.
        return $value === '0' || !empty($value);
    }
}
