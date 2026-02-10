<?php

declare(strict_types=1);

namespace Application\Admin\Welcome\Screens;

use Application\Admin\Area\BaseMode;
use Application\Admin\ClassLoaderScreenInterface;
use Application_User_Notepad;
use Application_User_Recent;
use Application_User_Recent_Category;
use Application_User_Recent_NoteCategory;
use UI;
use UI_Themes_Theme_ContentRenderer;

/**
 * @see template_default_content_welcome
 */
class OverviewMode extends BaseMode implements ClassLoaderScreenInterface
{
    public const string URL_NAME = 'overview';

    private Application_User_Recent $recent;

    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass(): null
    {
        return null;
    }

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getParentScreenClass(): string
    {
        return WelcomeArea::class;
    }

    public function getRequiredRight(): null
    {
        return null;
    }

    public function getNavigationTitle(): string
    {
        return t('Quickstart');
    }

    public function getTitle(): string
    {
        return t('Quickstart');
    }

    protected function _handleActions(): bool
    {
        $this->recent = $this->user->getRecent();

        $clearParam = Application_User_Recent_Category::REQUEST_PARAM_CLEAR_CATEGORY;
        $unpinParam = Application_User_Recent_NoteCategory::REQUEST_PARAM_UNPIN_NOTE;

        if ($this->request->hasParam($clearParam)) {
            $this->handleClearCategory((string)$this->request->getParam($clearParam));
        } else if ($this->request->hasParam($unpinParam)) {
            $this->handleUnpinNote((int)$this->request->getParam($unpinParam));
        }

        return true;
    }

    private function getCategoryLabels(): array
    {
        $categories = $this->recent->getCategories();
        $items = array();
        foreach ($categories as $category) {
            $items[] = $category->getLabel();
        }

        return $items;
    }

    protected function _handleHelp(): void
    {
        $this->help->setSummary(t('Your personal activity tracker'));

        $this->help->addPara(sb()
            ->t('The tracker will add elements you visit in %1$s to your quickstart, so you can easily find them again.', $this->driver->getAppNameShort())
            ->t('This includes the following categories:')
        );

        $this->help->addPara(sb()
            ->ul($this->getCategoryLabels())
        );

        $this->help->addPara(sb()
            ->t('Every time you visit the same element again, it is moved up to the top of the list.')
            ->t('This way, you can always see what you worked on last.')
            ->t(
                'By default, up to %1$s elements are shown in each category (you can customize this in the %2$ssettings%3$s).',
                $this->recent->getMaxItemsDefault(),
                '<a href="' . $this->recent->getAdminSettingsURL() . '">',
                '</a>'
            )
            ->t('The oldest elements are dropped off the end of the list when the maximum amount is reached.')
        );

        $this->help->addHeader(t('Pinning notes'));

        $this->help->addPara(sb()
            ->t('You can pin notes from your personal notepad to the quickstart.')
            ->t('Pinned notes are shown among the existing quickstart categories.')
            ->t('Pinning a note is easy:')
            ->ol(array(
                t(
                    'Open the %1$snotepad%2$s (will open above),',
                    '<a href="#" onclick="' . Application_User_Notepad::getJSOpen() . ';return false;">',
                    '</a>'
                ),
                sb()->t('Click a note\'s pin icon:')->add(UI::icon()->pin()->makeInformation()),
            ))
            ->t('You can unpin the note again from the quickstart screen.')
        );

        $this->help->addHeader(t('Turning off the quickstart'));

        $this->help->addPara(sb()
            ->t('The quickstart can not be turned off entirely.')
            ->t('However, you can change the page you see when you log in.')
            ->t(
                'For this, go into your %1$suser settings%2$s, and select the startup tab you would prefer.',
                '<a href="' . $this->user->getAdminSettingsURL() . '">',
                '</a>'
            )
            ->t('The quickstart will still be available should you need it later.')
        );

        $this->renderer->getTitle()
            ->setIcon($this->area->getNavigationIcon())
            ->addContextElement(
                UI::button(t('Open notepad'))
                    ->setIcon(UI::icon()->notepad())
                    ->setTooltip(Application_User_Notepad::getTooltipText())
                    ->click(Application_User_Notepad::getJSOpen())
            )
            ->addContextElement(
                UI::button(t('Settings'))
                    ->setIcon(UI::icon()->settings())
                    ->setTooltip(t('Open the quickstart settings.'))
                    ->link($this->recent->getAdminSettingsURL())
            );

        $this->renderer->setTitle(sb()
            ->add($this->getTitle())
        );
    }

    public function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        $tpl = $this->ui->createTemplate('content/welcome')
            ->setVar('user', $this->user)
            ->setVar('recent', $this->user->getRecent());

        return $this->renderer
            ->appendTemplate($tpl)
            ->makeWithoutSidebar();
    }

    private function handleClearCategory(string $categoryAlias): void
    {
        $category = $this->recent->getCategoryByAlias($categoryAlias);
        $category->clearEntries();

        $this->redirectWithSuccessMessage(
            t('The %1$s history has been cleared.', $category->getLabel()),
            $this->recent->getAdminURL()
        );
    }

    private function handleUnpinNote(int $noteID): void
    {
        $notepad = $this->user->getNotepad();

        if (!$notepad->idExists($noteID)) {
            return;
        }

        $this->recent->unpinNote($notepad->getNoteByID($noteID));

        $this->redirectWithSuccessMessage(
            t('The note has been unpinned.'),
            $this->recent->getAdminURL()
        );
    }
}
