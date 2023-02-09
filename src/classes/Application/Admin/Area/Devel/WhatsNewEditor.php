<?php
/**
 * File containing the class {@see Application_Admin_Area_Devel_WhatsNewEditor}.
 *
 * @package Application
 * @subpackage Admin
 * @see Application_Admin_Area_Devel_WhatsNewEditor
 */

declare(strict_types=1);

use Application\AppFactory;

/**
 * User interface for editing the `WHATSNEW.xml` file.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Admin_Area_Devel_WhatsNewEditor extends Application_Admin_Area_Mode
{
    public const URL_NAME = 'whatsneweditor';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getDefaultSubmode() : string
    {
        return Application_Admin_Area_Devel_WhatsNewEditor_List::URL_NAME;
    }

    public function isUserAllowed() : bool
    {
        return $this->user->isDeveloper();
    }

    public function getNavigationTitle() : string
    {
        return t('What\'s new editor');
    }

    public function getTitle() : string
    {
        return t('What\'s new editor');
    }

    protected function _handleHelp() : void
    {
        $this->renderer->setTitle($this->getTitle());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);

        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked(AppFactory::createWhatsNew()->getAdminListURL());
    }
}
