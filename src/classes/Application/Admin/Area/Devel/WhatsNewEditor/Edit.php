<?php
/**
 * File containing the class {@see Application_Admin_Area_Devel_WhatsNewEditor}.
 *
 * @package Application
 * @subpackage Admin
 * @see Application_Admin_Area_Devel_WhatsNewEditor
 */

declare(strict_types=1);

use Application\WhatsNew;

/**
 * User interface for editing the `WHATSNEW.xml` file.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Admin_Area_Devel_WhatsNewEditor_Edit extends Application_Admin_Area_Mode_Submode
{
    public const URL_NAME = 'edit';
    public const FORM_NAME = 'edit-version';

    private WhatsNew $whatsNew;

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function isUserAllowed() : bool
    {
        return $this->user->isDeveloper();
    }

    public function getNavigationTitle() : string
    {
        return t('Edit');
    }

    public function getTitle() : string
    {
        return t('Edit a version');
    }

    public function getDefaultAction() : string
    {
        return '';
    }

    protected function _handleActions() : bool
    {
        $this->whatsNew = Application_Driver::createWhatsnew();

        $this->createSettingsForm();

        return true;
    }

    protected function _handleSidebar() : void
    {
    }

    protected function _renderContent() : UI_Renderable_Interface
    {
        return $this->renderer
            ->appendFormable($this)
            ->makeWithSidebar();
    }

    private function createSettingsForm() : void
    {
        $this->createFormableForm(self::FORM_NAME);

        $this->injectCurrentVersion();
        $this->injectVersion();
    }

    private function injectCurrentVersion() : void
    {
        $current = $this->whatsNew->getCurrentVersion();

        if($current === null)
        {
            return;
        }

        $this->addElementStatic(
            t('Current version'),
            $current->getNumber()
        );
    }

    private function injectVersion() : void
    {
        $el = $this->addElementText('version', t('New version'));
        $el->addClass('input-small');


    }
}
