<?php

declare(strict_types=1);

namespace Application\Renamer;

use Application\Renamer\Admin\RenamerAdminURLs;
use Application\Renamer\Index\RenamerIndex;
use Application\Renamer\Index\RenamerIndexRunner;
use Application_Interfaces_Formable;
use AppUtils\ClassHelper;
use DBHelper;
use Maileditor\Renamer\RenamerConfig;

class RenamingManager
{
    private DataColumnsCollection $columns;

    private static ?RenamingManager $instance = null;

    public static function getInstance() : RenamingManager
    {
        if(self::$instance === null) {
            self::$instance = new RenamingManager();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->columns = new DataColumnsCollection();
    }

    private ?RenamerAdminURLs $adminURLs = null;

    public function adminURL() : RenamerAdminURLs
    {
        if(!isset($this->adminURLs)) {
            $this->adminURLs = new RenamerAdminURLs();
        }

        return $this->adminURLs;
    }

    public static function getName() : string
    {
        return t('DB Renamer');
    }

    public function getColumns(): DataColumnsCollection
    {
        return $this->columns;
    }

    public function createSettingsForm(Application_Interfaces_Formable $formable) : RenamerSettingsManager
    {
        return new RenamerSettingsManager($this, $formable);
    }

    public function createSearchRunner(RenamerConfig $config) : RenamerIndexRunner
    {
        return new RenamerIndexRunner($this, $config->getSearch(), $config->getColumnIDs(), $config->isCaseSensitive());
    }

    public function createCollection() : RenamerIndex
    {
        return ClassHelper::requireObjectInstanceOf(
            RenamerIndex::class,
            DBHelper::createCollection(RenamerIndex::class)
        );
    }
}
