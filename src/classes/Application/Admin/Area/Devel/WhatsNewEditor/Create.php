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
use Application\WhatsNew;

/**
 * User interface for editing the `WHATSNEW.xml` file.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Admin_Area_Devel_WhatsNewEditor_Create extends Application_Admin_Area_Mode_Submode
{
    public const URL_NAME = 'create';
    public const FORM_NAME = 'create-version';
    public const ELEMENT_VERSION = 'version';

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
        return t('Create');
    }

    public function getTitle() : string
    {
        return t('Create a new version');
    }

    public function getDefaultAction() : string
    {
        return '';
    }

    protected function _handleActions() : bool
    {
        $this->whatsNew = AppFactory::createWhatsNew();

        $this->createSettingsForm();

        if($this->isFormValid())
        {
            $values = $this->getFormValues();
            $this->handleCreateVersion($values[self::ELEMENT_VERSION]);
        }

        return true;
    }

    protected function _handleSidebar() : void
    {
        $this->sidebar->addButton('save-new', t('Create now'))
            ->makePrimary()
            ->setIcon(UI::icon()->add())
            ->makeClickableSubmit($this);

        $this->sidebar->addButton('cancel', t('Cancel'))
            ->link($this->whatsNew->getAdminListURL());
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

        $this->setDefaultElement(self::ELEMENT_VERSION);
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
        $el = $this->addElementText(self::ELEMENT_VERSION, t('New version'));
        $el->addClass('input-small');

        $this->makeRequired($el);
    }

    private function handleCreateVersion(string $version) : void
    {
        $new = $this->whatsNew->addVersion($version);

        $this->whatsNew->writeToDisk();

        $this->redirectWithSuccessMessage(
            t(
                'The version %1$s was created successfully at %2$s.',
                $new->getNumber(),
                sb()->time()
            ),
            $new->getAdminEditURL()
        );
    }
}
