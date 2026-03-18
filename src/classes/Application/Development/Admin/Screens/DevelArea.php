<?php

declare(strict_types=1);

namespace Application\Development\Admin\Screens;

use Application\Admin\BaseArea;
use Application\Admin\Traits\DevelModeInterface;
use Application\Development\Admin\DevScreenRights;
use Application\Interfaces\Admin\AdminScreenInterface;
use UI;
use UI_Icon;
use UI_Page_Navigation;

/**
 * Developer area: Creates the navigation for all available development
 * screens. These are dispatched throughout the application's modules.
 *
 * Screens and their subscreens are registered automatically as soon as
 * they extend the interface {@see DevelModeInterface}.
 *
 * @package
 */
class DevelArea extends BaseArea
{
    public const string URL_NAME = 'devel';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getDefaultMode(): string
    {
        return DevelOverviewMode::URL_NAME;
    }

    public function getDefaultSubscreenClass(): string
    {
        return DevelOverviewMode::class;
    }

    public function getRequiredRight(): string
    {
        return DevScreenRights::SCREEN_DEVEL;
    }

    public function getTitle(): string
    {
        return t('Developer tools');
    }

    public function getNavigationTitle(): string
    {
        return t('Developer tools');
    }

    public function getNavigationGroup(): string
    {
        return t('Manage');
    }

    public function getNavigationIcon(): ?UI_Icon
    {
        return UI::icon()->developer();
    }

    public function isCore(): bool
    {
        return true;
    }

    public function getDependencies(): array
    {
        return array();
    }

    protected function initItems(): void
    {
        // Instantiate screens in non-admin mode: admin-mode instantiation triggers
        // initScreen() → setParam('mode', urlName) as a side effect, which would
        // clobber $_REQUEST['mode'] for the active screen. For navigation purposes
        // we only need URL name, title and category — none of which require admin mode.
        foreach($this->getSubscreenIDs() as $subscreenID => $urlName)
        {
            $subscreen = $this->createSubscreen($subscreenID, false);

            if($subscreen instanceof DevelOverviewMode) {
                continue;
            }

            if($subscreen instanceof DevelModeInterface) {
                $this->registerItem(
                    $subscreen->getURLName(),
                    $subscreen->getNavigationTitle(),
                    $subscreen->getDevCategory()
                );
            }
        }

        ksort($this->items);

        // sort each category's items by label
        foreach ($this->items as &$categoryItems) {
            asort($categoryItems);
        }
    }

    /**
     * @var array<string, array<string,string>>|NULL Category label => array of URL name => label pairs
     */
    protected ?array $items = null;

    /**
     * @return array<string, array<string,string>>
     */
    public function getItems(): array
    {
        if (!isset($this->items)) {
            $this->items = array();
            $this->initItems();
        }

        return $this->items;
    }

    protected function registerItem(string $urlName, string $label, string $categoryLabel = ''): void
    {
        if (empty($categoryLabel)) {
            $categoryLabel = t('Miscellaneous');
        }

        if (!isset($this->items[$categoryLabel])) {
            $this->items[$categoryLabel] = array();
        }

        $this->items[$categoryLabel][$urlName] = $label;
    }

    protected function _handleSubnavigation(): void
    {
        $this->injectSubnavigation($this->subnav);
    }

    public function injectSubnavigation(UI_Page_Navigation $subnav) : void
    {
        $items = array_merge(
            array(
                DevelOverviewMode::URL_NAME => (string)UI::icon()->home()->setTooltip(t('Overview'))
            ),
            $this->getItems()
        );

        foreach ($items as $mode => $title) {
            if (is_array($title)) {
                foreach ($title as $mmode => $ttitle) {
                    $subnav->addURL($ttitle, $this->getURL(array(AdminScreenInterface::REQUEST_PARAM_MODE => $mmode)))
                        ->setGroup($mode);
                }

                continue;
            }

            $subnav->addURL($title, $this->getURL(array(AdminScreenInterface::REQUEST_PARAM_MODE => $mode)));
        }
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this);
    }
}