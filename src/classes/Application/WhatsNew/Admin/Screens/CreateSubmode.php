<?php
/**
 * @package Application
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\WhatsNew\Admin\Screens;

use Application\Admin\Area\Mode\BaseSubmode;
use Application\AppFactory;
use Application\WhatsNew\Admin\Traits\WhatsNewSubmodeInterface;
use Application\WhatsNew\Admin\Traits\WhatsNewSubmodeTrait;
use Application\WhatsNew\Admin\WhatsNewScreenRights;
use Application\WhatsNew\WhatsNew;
use UI;
use UI_Renderable_Interface;

/**
 * User interface for editing the `WHATSNEW.xml` file.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class CreateSubmode extends BaseSubmode implements WhatsNewSubmodeInterface
{
    use WhatsNewSubmodeTrait;

    public const string URL_NAME = 'create';
    public const string FORM_NAME = 'create-version';
    public const string ELEMENT_VERSION = 'version';

    private WhatsNew $whatsNew;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): ?string
    {
        return WhatsNewScreenRights::SCREEN_CREATE;
    }

    public function getNavigationTitle(): string
    {
        return t('Create');
    }

    public function getTitle(): string
    {
        return t('Create a new version');
    }



    protected function _handleActions(): bool
    {
        $this->whatsNew = AppFactory::createWhatsNew();

        $this->createSettingsForm();

        if ($this->isFormValid()) {
            $values = $this->getFormValues();
            $this->handleCreateVersion($values[self::ELEMENT_VERSION]);
        }

        return true;
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('save-new', t('Create now'))
            ->makePrimary()
            ->setIcon(UI::icon()->add())
            ->makeClickableSubmit($this);

        $this->sidebar->addButton('cancel', t('Cancel'))
            ->link($this->whatsNew->getAdminListURL());
    }

    protected function _renderContent(): UI_Renderable_Interface
    {
        return $this->renderer
            ->appendFormable($this)
            ->makeWithSidebar();
    }

    private function createSettingsForm(): void
    {
        $this->createFormableForm(self::FORM_NAME);

        $this->injectCurrentVersion();
        $this->injectVersion();

        $this->setDefaultElement(self::ELEMENT_VERSION);
    }

    private function injectCurrentVersion(): void
    {
        $current = $this->whatsNew->getCurrentVersion();

        if ($current === null) {
            return;
        }

        $this->addElementStatic(
            t('Current version'),
            $current->getNumber()
        );
    }

    private function injectVersion(): void
    {
        $el = $this->addElementText(self::ELEMENT_VERSION, t('New version'));
        $el->addClass('input-small');

        $this->makeRequired($el);
    }

    private function handleCreateVersion(string $version): void
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
