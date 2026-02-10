<?php
/**
 * @package Application
 * @subpackage Appsets
 */

declare(strict_types=1);

namespace Application\AppSets;

use Application\Admin\Index\AdminScreenIndex;
use Application\Admin\Welcome\Screens\WelcomeArea;
use Application\AppFactory;
use Application\AppSets\Admin\AppSetAdminURLs;
use Application\Interfaces\Admin\AdminAreaInterface;
use Application\MarkdownRenderer\MarkdownRenderer;
use Application\Sets\Admin\AppSetScreenRights;
use Application_Driver;
use Application_Formable;
use AppUtils\ConvertHelper;
use DBHelper_BaseRecord;
use UI;

/**
 * Container for a single application set. Provides an API
 * for accessing set information and manipulating it. Use the
 * sets collection's {@link AppSetsCollection::getByID()} method
 * to retrieve a specific set.
 *
 * @package Application
 * @subpackage Appsets
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class AppSet extends DBHelper_BaseRecord
{
    public const string KEY_DEFAULT_URL_NAME = 'defaultArea';
    public const string KEY_ID = 'id';

    public const string SETTING_ID = 'id';
    public const string KEY_ENABLED = 'enabled';

    protected string $id;
    protected ?AdminAreaInterface $defaultArea = null;

    public function isDefault() : bool
    {
        return $this instanceof DefaultAppSet;
    }

    public function getLabel(): string
    {
        return $this->getRecordStringKey(AppSetsCollection::COL_LABEL);
    }

    public function getAlias(): string
    {
        return $this->getRecordStringKey(AppSetsCollection::COL_ALIAS);
    }

    public function getDescription(): string
    {
        return $this->getRecordStringKey(AppSetsCollection::COL_DESCRIPTION);
    }

    /**
     * The default area that should be opened when this set is active.
     * @return AdminAreaInterface
     */
    public function getDefaultArea(): AdminAreaInterface
    {
        if(!isset($this->defaultArea)) {
            $this->defaultArea = AppFactory::createDriver()->createArea($this->getDefaultURLName());
        }

        return $this->defaultArea;
    }

    public function getDefaultURLName() : string
    {
        $id = $this->getRecordStringKey(AppSetsCollection::COL_DEFAULT_URL_NAME);

        $index = AdminScreenIndex::getInstance();

        if($index->areaExists($id)) {
            return $id;
        }

        return WelcomeArea::URL_NAME;
    }

    public static function createSettingsForm(Application_Formable $formable, ?AppSet $set = null): void
    {

    }

    /**
     * @var array<string,AdminAreaInterface> URL Path => AdminAreaInterface pairs.
     */
    protected array $enabled = array();

    /**
     * Enables the specified area for the set.
     *
     * @param AdminAreaInterface $area
     * @return $this
     */
    public function enableArea(AdminAreaInterface $area): self
    {
        // Core areas are always enabled.
        if($area->isCore()) {
            return $this;
        }

        $this->initEnabledAreas();

        $id = $area->getURLName();

        if (!isset($this->enabled[$id])) {
            $this->enabled[$id] = $area;
        }

        return $this;
    }

    private bool $areasInitialized = false;

    private function initEnabledAreas() : void
    {
        if($this->areasInitialized) {
            return;
        }

        $this->areasInitialized = true;

        $driver = Application_Driver::getInstance();

        foreach ($this->getEnabledAreaURLNames() as $areaID) {
            $this->enableArea($driver->createArea($areaID));
        }
    }

    /**
     * Gets the stored list of enabled area URL names,
     * filtering out any that do not exist, or may have
     * been promoted to core area.
     *
     * @return string[] List of non-core valid area URL names.
     */
    public function getEnabledAreaURLNames() : array
    {
        $urlNames = ConvertHelper::explodeTrim(',', $this->getRecordStringKey(AppSetsCollection::COL_URL_NAMES));
        $index = AdminScreenIndex::getInstance();
        $driver = AppFactory::createDriver();

        $result = array();
        foreach($urlNames as $urlName)
        {
            // The area may not exist anymore, or has been renamed
            if(!$index->areaExists($urlName)) {
                continue;
            }

            $area = $driver->createArea($urlName);

            // The area may have been promoted to core in the meantime
            if($area->isCore()) {
                continue;
            }

            $result[] = $urlName;
        }

        return $result;
    }

    /**
     * Retrieves all areas enabled for this set.
     * @param bool $includeCore
     * @return AdminAreaInterface[]
     */
    public function getEnabledAreas(bool $includeCore = true) : array
    {
        $this->initEnabledAreas();

        $result = array();

        if ($includeCore) {
            $areas = Application_Driver::getInstance()->getAdminAreaObjects();
            foreach ($areas as $area) {
                if ($area->isCore()) {
                    $result[] = $area;
                }
            }
        }

        array_push($result, ...array_values($this->enabled));

        return $result;
    }

    /**
     * Retrieves the URL names of all areas that
     * are currently enabled.
     *
     * @return string[]
     */
    public function getEnabledURLNames() : array
    {
        $result = array();

        foreach ($this->getEnabledAreas() as $area) {
            $result[] = $area->getURLName();
        }

        sort($result);

        return $result;
    }

    /**
     * Retrieves the human-readable names (titles) of all
     * areas that are currently enabled.
     *
     * @return string[]
     */
    public function getEnabledAreaLabels() : array
    {
        $result = array();

        foreach ($this->getEnabledAreas() as $area) {
            $result[] = $area->getTitle();
        }

        usort($result, 'strnatcasecmp');

        return $result;
    }

    /**
     * Checks whether the specified area is enabled for this
     * application set. Core areas are always enabled.
     *
     * @param AdminAreaInterface $area
     * @return boolean
     */
    public function isAreaEnabled(AdminAreaInterface $area) : bool
    {
        $this->initEnabledAreas();

        return in_array($area, $this->getEnabledAreas());
    }

    /**
     * Enables a collection of areas at once.
     * @param AdminAreaInterface[] $areas
     * @return $this
     */
    public function enableAreas(array $areas) : self
    {
        foreach ($areas as $area) {
            $this->enableArea($area);
        }

        return $this;
    }

    /**
     * Whether this is the currently active application set.
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->getID() === AppFactory::createAppSets()->getActiveID();
    }

    public function areAllAreasEnabled() : bool
    {
        return $this->getEnabledURLNames() === AdminScreenIndex::getInstance()->getAdminAreaURLNames();
    }

    public function adminURL() : AppSetAdminURLs
    {
        return new AppSetAdminURLs($this);
    }

    public function getLabelLinked() : string
    {
        return (string)sb()->linkRight(
            $this->getLabel(),
            $this->adminURL()->status(),
            AppSetScreenRights::SCREEN_VIEW_STATUS
        );
    }

    public function getActiveBadge() : string
    {
        if($this->isActive()) {
            return (string)UI::label('')
                ->setIcon(UI::icon()->ok())
                ->makeSuccess();
        }

        return (string)UI::icon()->notAvailable()->makeMuted();
    }

    public function renderDocumentation() : string
    {
        $description = $this->getDescription();
        if(empty($description)) {
            return (string)sb()->muted(sb()->parentheses(t('No documentation available.')));
        }

        return MarkdownRenderer::create()->render($description);
    }

    public function hasDocumentation() : bool
    {
        return !empty($this->getDescription());
    }

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue) : void
    {
    }
}
