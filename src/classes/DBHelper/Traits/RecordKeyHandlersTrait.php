<?php

declare(strict_types=1);

namespace DBHelper\Traits;

use Application\Disposables\DisposableDisposedException;
use AppUtils\ConvertHelper;
use AppUtils\Microtime;
use DateTime;
use DBHelper\BaseRecord\BaseRecordException;

trait RecordKeyHandlersTrait
{
    /**
     * Retrieves a data key as an integer. Converts the value to int,
     * so beware using this on non-integer keys.
     *
     * @param string $name
     * @param int $default
     * @return int
     * @throws DisposableDisposedException
     */
    public function getRecordIntKey(string $name, int $default=0) : int
    {
        $value = $this->getRecordKey($name);
        if($value !== null && $value !== '') {
            return (int)$value;
        }

        return $default;
    }

    public function getRecordFloatKey(string $name, float $default=0.0) : float
    {
        $value = $this->getRecordKey($name);
        if($value !== null && $value !== '') {
            return (float)$value;
        }

        return $default;
    }

    public function getRecordStringKey(string $name, string $default='') : string
    {
        $value = $this->getRecordKey($name);
        if(!empty($value) && is_string($value)) {
            return $value;
        }

        return $default;
    }

    public function getRecordDateKey(string $name, ?DateTime $default=null) : ?DateTime
    {
        $value = $this->getRecordStringKey($name);
        if(!empty($value)) {
            return new DateTime($value);
        }

        return $default;
    }

    public function getRecordMicrotimeKey(string $name) : ?Microtime
    {
        $value = $this->getRecordStringKey($name);
        if(!empty($value)) {
            return Microtime::createFromString($value);
        }

        return null;
    }

    public function requireRecordMicrotimeKey(string $name) : Microtime
    {
        $value = $this->getRecordMicrotimeKey($name);
        if($value !== null) {
            return $value;
        }

        throw new BaseRecordException(
            'No microtime date value available.',
            sprintf(
                'The record key [%s] does not contain a valid microtime date value in the record [%s].',
                $name,
                $this->getIdentification()
            ),
            BaseRecordException::ERROR_RECORD_KEY_INVALID_MICROTIME
        );
    }

    public function getRecordBooleanKey(string $name, bool $default=false) : bool
    {
        $value = $this->getRecordKey($name, $default);
        if($value===null) {
            $value = $default;
        }

        return ConvertHelper::string2bool($value);
    }

    public function setRecordBooleanKey(string $name, bool $boolean, bool $yesno=true) : bool
    {
        $value = ConvertHelper::boolStrict2string($boolean, $yesno);
        return $this->setRecordKey($name, $value);
    }

    public function setRecordDateKey(string $name, DateTime $date) : bool
    {
        return $this->setRecordKey($name, $date->format('Y-m-d H:i:s'));
    }
}
