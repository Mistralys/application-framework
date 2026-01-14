<?php

declare(strict_types=1);

namespace NewsCentral\Entries\Criticalities;

use Application_Interfaces_Loggable;
use Application_Traits_Loggable;
use AppUtils\Collections\BaseStringPrimaryCollection;

/**
 * @method NewsEntryCriticality getByID(string $id)
 * @method NewsEntryCriticality[] getAll()
 * @method NewsEntryCriticality getDefault()
 */
class NewsEntryCriticalities extends BaseStringPrimaryCollection implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const string CRITICALITY_INFO = 'info';
    public const string CRITICALITY_WARNING = 'warning';
    public const string CRITICALITY_CRITICAL = 'critical';

    public const string DEFAULT_CRITICALITY = self::CRITICALITY_INFO;

    private static ?NewsEntryCriticalities $instance = null;

    public static function getInstance() : NewsEntryCriticalities
    {
        if(isset(self::$instance)) {
            return self::$instance;
        }

        $instance = new self();

        self::$instance = $instance;

        return $instance;
    }

    public function getWarning() : NewsEntryCriticality
    {
        return $this->getByID(self::CRITICALITY_WARNING);
    }

    public function getInfo() : NewsEntryCriticality
    {
        return $this->getByID(self::CRITICALITY_INFO);
    }

    public function getCritical() : NewsEntryCriticality
    {
        return $this->getByID(self::CRITICALITY_CRITICAL);
    }

    protected function registerItems(): void
    {
        $this->registerItem(new NewsEntryCriticality(
            self::CRITICALITY_INFO,
            t('Informational')
        ));

        $this->registerItem(new NewsEntryCriticality(
            self::CRITICALITY_WARNING,
            t('Warning')
        ));

        $this->registerItem(new NewsEntryCriticality(
            self::CRITICALITY_CRITICAL,
            t('Critical')
        ));
    }

    public function getDefaultID(): string
    {
        return self::DEFAULT_CRITICALITY;
    }

    public function getLogIdentifier(): string
    {
        return 'NewsEntryCriticalities';
    }
}
