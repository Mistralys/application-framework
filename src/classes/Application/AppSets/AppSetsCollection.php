<?php
/**
 * @package Application
 * @subpackage Sets
 */

declare(strict_types=1);

namespace Application\AppSets;

use Application\AppFactory;
use Application\Interfaces\Admin\AdminAreaInterface;
use Application\Sets\AppSetsException;
use Application_Driver;
use AppUtils\ClassHelper;
use AppUtils\RegexHelper;
use DBHelper;
use DBHelper\Attributes\UncachedQuery;
use DBHelper_BaseCollection;
use DBHelper_StatementBuilder;
use UI;
use UI_Page_Sidebar;

/**
 * Helper class used to manage application sets: these
 * can be used to create different application UI
 * environments with only specific administration areas.
 *
 * @package Application
 * @subpackage Sets
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method AppSet[] getAll()
 */
class AppSetsCollection extends DBHelper_BaseCollection
{
    public const string PRIMARY_NAME = 'app_set_id';
    public const string TABLE_NAME = 'app_sets';

    public const string COL_ALIAS = 'alias';
    public const string COL_IS_ACTIVE = 'is_active';
    public const string COL_LABEL = 'label';
    public const string COL_DESCRIPTION = 'description';
    public const string COL_DEFAULT_URL_NAME = 'default_url_name';
    public const string COL_URL_NAMES = 'enabled_url_names';

    public const int DEFAULT_ID = -690; // 690 AD: Wu Zetian becomes Empress Regnant of China.
    public const string RECORD_TYPE = 'application_set';
    public const string REGEX_ALIAS = RegexHelper::REGEX_ALIAS_CAPITALS;
    public const string REQUEST_PRIMARY_NAME = 'appSet';
    public const string DEFAULT_ALIAS = '__default';

    /**
     * @var AppSet[]
     */
    protected array $sets = array();

    /**
     * @return AppSetsCollection
     */
    public static function getInstance(): AppSetsCollection
    {
        return ClassHelper::requireObjectInstanceOf(
            self::class,
            DBHelper::createCollection(self::class)
        );
    }

    public function getAdminListURL(array $params = array()): string
    {
        $params['submode'] = 'list';
        return $this->getAdminURL($params);
    }

    public function getAdminCreateURL(array $params = array()): string
    {
        $params['submode'] = 'create';
        return $this->getAdminURL($params);
    }

    protected function getAdminURL(array $params = array()): string
    {
        $params['page'] = 'devel';
        $params['mode'] = 'appsets';

        return Application_Driver::getInstance()->getRequest()->buildURL($params);
    }

    public static function statement(string $template) : DBHelper_StatementBuilder
    {
        return statementBuilder($template, AppSetsFilterCriteria::getValues());
    }

    #[UncachedQuery]
    public function aliasExists(string $alias) : bool
    {
        return $this->getIDByAlias($alias) !== null;
    }

    #[UncachedQuery]
    public function getIDByAlias(string $alias) : ?int
    {
        $id = DBHelper::fetchKeyInt(
            self::PRIMARY_NAME,
            self::statement(/** @lang text */"
            SELECT
                {set_primary}
            FROM
                {set_table}
            WHERE
                {col_alias} = :alias"
            ),
            array(
                'alias' => $alias
            )
        );

        if($id > 0) {
            return $id;
        }

        return null;
    }

    public function createNewRecord(array $data = array(), bool $silent = false, array $options = array()): AppSet
    {
        return ClassHelper::requireObjectInstanceOf(
            AppSet::class,
            parent::createNewRecord($data, $silent, $options)
        );
    }

    /**
     * Creates a new application set and returns the instance.
     *
     * @param string $alias
     * @param string $label
     * @param AdminAreaInterface $defaultArea
     * @param AdminAreaInterface[] $enabledAreas
     * @return AppSet
     * @throws AppSetsException
     */
    public function createNew(string $alias, string $label, AdminAreaInterface $defaultArea, array $enabledAreas = array()): AppSet
    {
        if ($this->aliasExists($alias)) {
            throw new AppSetsException(
                'Application set alias already exists',
                sprintf(
                    'Cannot create a new set with alias [%s], this alias is already in use by an existing application set.',
                    $alias
                ),
                AppSetsException::ERROR_SET_ID_ALREADY_EXISTS
            );
        }

        $enabledNames = array();
        foreach ($enabledAreas as $area) {
            $enabledNames[] = $area->getURLName();
        }

        return $this->createNewRecord(array(
            self::COL_ALIAS => $alias,
            self::COL_LABEL => $label,
            self::COL_URL_NAMES => implode(',', $enabledNames)
        ));
    }

    public function idExists(int $record_id): bool
    {
        return $record_id === self::DEFAULT_ID || parent::idExists($record_id);
    }

    protected ?AppSet $default = null;

    public function getByID(int $record_id): AppSet|DefaultAppSet
    {
        if ($record_id === self::DEFAULT_ID) {
            return $this->getDefaultSet();
        }

        return ClassHelper::requireObjectInstanceOf(
            AppSet::class,
            parent::getByID($record_id)
        );
    }

    /**
     * Gets the default application set in which all admin
     * areas are enabled.
     *
     * @return DefaultAppSet
     */
    public function getDefaultSet(): DefaultAppSet
    {
        if (!isset($this->default)) {
            $this->default = new DefaultAppSet();
        }

        return $this->default;
    }

    /**
     * Saves all application sets to the configuration file.
     *
     * @throws AppSetsException
     * @deprecated
     */
    public function save(): void
    {

    }

    /**
     * Renames the ID of a set. Called by a set when
     * it is renamed, do not call this manually.
     *
     * @param AppSet $set
     * @param string $newID
     * @throws AppSetsException
     */
    public function handle_renameSet(AppSet $set, string $newID): void
    {
        if (isset($this->sets[$newID])) {
            throw new AppSetsException(
                'Cannot rename set, same name already exists',
                sprintf(
                    'Cannot rename set [%s] to [%s], that set already exists.',
                    $oldID,
                    $newID
                ),
                AppSetsException::ERROR_CANNOT_RENAME_TO_EXISTING_NAME
            );
        }

        unset($this->sets[$set->getID()]);

        $this->sets[$newID] = $set;
    }

    public function getActiveID() : int
    {
        $id = DBHelper::fetchKeyInt(
            self::PRIMARY_NAME,
            self::statement(/** @lang text */"
            SELECT
                {set_primary}
            FROM
                {set_table}
            WHERE
                {col_is_active} = 'yes'
            LIMIT 1"
            )
        );

        if($id > 0) {
            return $id;
        }

        return self::DEFAULT_ID;
    }

    public function getActive() : AppSet|DefaultAppSet
    {
        return $this->getByID($this->getActiveID());
    }


    public function injectCoreAreas(UI_Page_Sidebar $sidebar) : void
    {
        $sidebar->addSeparator();

        $sidebar->addHelp(
            t('Core areas'),
            sb()
                ->para(t('These admin areas are always available and cannot be turned off:'))
                ->ul($this->getCoreAreasList()),
            false
        );
    }

    private function getCoreAreasList() : array
    {
        $list = array();
        foreach(AppFactory::createDriver()->getAreas() as $area) {
            if(!$area->isCore()) {
                continue;
            }

            $title = $area->getTitle();

            if($area->isLocatedInApp()) {
                $title .= ' '. UI::label(t('APP'))
                    ->setTooltip(t(
                        'This area is provided by the %1$s application.',
                        AppFactory::createDriver()->getAppNameShort()
                    ));
            }

            $list[] = $title;
        }

        usort($list, 'strnatcasecmp');

        return $list;
    }

    /**
     * Marks the given application set as active.
     *
     * > NOTE: This is intended to be called and
     * > the request terminated. It does not update
     * > in-memory references of loaded sets.
     *
     * @param AppSet $set
     * @return void
     */
    public function makeSetActive(AppSet $set) : void
    {
        DBHelper::requireTransaction('Enable an application set');

        // Reset all sets to inactive
        DBHelper::updateDynamic(
            self::TABLE_NAME,
            array(
                self::COL_IS_ACTIVE => 'no'
            ),
            array()
        );

        // Activate the selected set
        DBHelper::updateDynamic(
            self::TABLE_NAME,
            array(
                self::COL_IS_ACTIVE => 'yes',
                self::PRIMARY_NAME => $set->getID()
            ),
            array(
                self::PRIMARY_NAME
            )
        );
    }

    // region: Interface methods

    protected function _registerKeys(): void
    {
        $this->keys->register(self::COL_ALIAS)
            ->setRegexValidation(self::REGEX_ALIAS)
            ->makeRequired();

        $this->keys->register(self::COL_LABEL)
            ->makeRequired();

        $this->keys->register(self::COL_IS_ACTIVE)
            ->setDefault('no')
            ->setEnumValidation(array('yes', 'no'));
    }

    public function getRecordClassName(): string
    {
        return AppSet::class;
    }

    public function getRecordRequestPrimaryName(): string
    {
        return self::REQUEST_PRIMARY_NAME;
    }

    public function getRecordFiltersClassName(): string
    {
        return AppSetsFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return AppSetsFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return self::COL_LABEL;
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            self::COL_ALIAS => t('Alias'),
            self::COL_LABEL => t('Label'),
            self::COL_DESCRIPTION => t('Description')
        );
    }

    public function getRecordTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getRecordPrimaryName(): string
    {
        return self::PRIMARY_NAME;
    }

    public function getRecordTypeName(): string
    {
        return self::RECORD_TYPE;
    }

    public function getCollectionLabel(): string
    {
        return t('Application sets');
    }

    public function getRecordLabel(): string
    {
        return t('Application set');
    }

    // endregion
}
