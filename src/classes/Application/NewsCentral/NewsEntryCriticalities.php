<?php

declare(strict_types=1);

namespace Application\NewsCentral;

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

    public const CRITICALITY_INFO = 'info';
    public const CRITICALITY_WARNING = 'warning';
    public const CRITICALITY_CRITICAL = 'critical';

    public const DEFAULT_CRITICALITY = self::CRITICALITY_INFO;

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
