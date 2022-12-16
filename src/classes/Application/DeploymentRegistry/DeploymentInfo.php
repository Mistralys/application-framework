<?php

declare(strict_types=1);

namespace Application\DeploymentRegistry;

use AppUtils\Microtime;
use AppUtils\Microtime_Exception;

class DeploymentInfo
{
    public const KEY_VERSION = 'version';
    public const KEY_DATE = 'date';

    private string $version;
    private Microtime $date;

    public function __construct(string $version, Microtime $date)
    {
        $this->version = $version;
        $this->date = $date;
    }

    public function getDate(): Microtime
    {
        return $this->date;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param array{version:string,date:string} $data
     * @return DeploymentInfo
     * @throws Microtime_Exception
     */
    public static function fromArray(array $data) : DeploymentInfo
    {
        return new DeploymentInfo(
            (string)$data[self::KEY_VERSION],
            Microtime::createFromString((string)$data[self::KEY_DATE])
        );
    }
}
