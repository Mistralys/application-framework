<?php
/**
 * @package Application
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\WhatsNew\Admin\Screens;

use Application\Admin\Area\Mode\BaseSubmode;
use Application\AppFactory;
use Application\MarkdownRenderer\MarkdownRenderer;
use Application\WhatsNew\Admin\Traits\WhatsNewSubmodeInterface;
use Application\WhatsNew\Admin\Traits\WhatsNewSubmodeTrait;
use Application\WhatsNew\Admin\WhatsNewScreenRights;
use Application\WhatsNew\AppVersion;
use Application\WhatsNew\AppVersion\CategoryItem;
use Application\WhatsNew\AppVersion\VersionLanguage;
use Application\WhatsNew\WhatsNew;
use Application_Driver;
use AppUtils\OutputBuffering;
use UI;
use UI_Renderable_Interface;

/**
 * User interface for editing the `WHATSNEW.xml` file.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class EditSubmode extends BaseSubmode implements WhatsNewSubmodeInterface
{
    use WhatsNewSubmodeTrait;

    public const string URL_NAME = 'edit';
    public const string FORM_NAME = 'edit-version';

    public const string KEY_NEW_CATEGORY = 'new-category';
    public const string KEY_NEW_TEXT = 'new-text';
    public const string KEY_NEW_AUTHOR = 'new-author';
    public const string KEY_NEW_ISSUE = 'new-issue';

    /**
     * @var CategoryItem[]
     */
    private array $items;
    protected WhatsNew $whatsNew;
    protected AppVersion $version;
    protected string $activeLanguageID;
    protected VersionLanguage $activeLanguage;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return WhatsNewScreenRights::SCREEN_EDIT;
    }

    public function getNavigationTitle(): string
    {
        return t('Edit');
    }

    public function getTitle(): string
    {
        return t('Edit a version');
    }

    protected function _handleActions(): bool
    {
        $this->whatsNew = AppFactory::createWhatsNew();

        $this->resolveVersion();
        $this->resolveActiveLanguage();

        $this->items = $this->activeLanguage->getItems();

        $this->createSettingsForm();

        if ($this->isFormValid()) {
            $values = $this->getFormValues();
            $this->processForm($values);
        }

        return true;
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('save', t('Save now'))
                ->makePrimary()
                ->setIcon(UI::icon()->save())
                ->makeClickableSubmit($this);

        $this->sidebar->addButton('cancel', t('Cancel'))
                ->makeLinked($this->whatsNew->getAdminListURL());

        $this->sidebar->addSeparator();

        $this->sidebar->addButton('view-as-text', t('Developer changelog'))
                ->setIcon(UI::icon()->text())
                ->setTooltip(t('Displays the developer changelog in plain text.'))
                ->makeLinked(Application_Driver::getInstance()->getAdminURLChangelog(), true);

        $this->sidebar->addSeparator();

        $this->addSidebarImagesList();
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setSubtitle(t('v%1$s', $this->version->getNumber()));
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem(t('v%1$s', $this->version->getNumber()))
                ->makeLinked($this->version->getAdminEditURL());

        $this->breadcrumb->appendItem($this->activeLanguageID)
                ->makeLinked($this->version->getAdminLanguageURL($this->activeLanguageID));
    }

    protected function _handleTabs(): void
    {
        $languages = VersionLanguage::getLanguageIDs();

        foreach($languages as $language)
        {
            $this->tabs->appendTab($language, 'lang-'.$language)
                    ->makeLinked($this->version->getAdminLanguageURL($language));
        }

        $this->tabs->selectTab($this->tabs->getTabByName('lang-'.$this->activeLanguageID));
    }

    protected function addSidebarImagesList(): void
    {
        $images = $this->whatsNew->getAvailableImages();

        if (!empty($images)) {
            OutputBuffering::start();

            ?>
            <ul class="unstyled">
                <?php
                foreach ($images as $image) {
                    ?>
                    <li>
                        <?php
                        echo sb()
                                ->link((string)sb()->mono($image->getName()), $image->getURL(), true)
                                ->muted(sprintf('%s x %s', $image->getWidth(), $image->getHeight()));
                        ?>
                    </li>
                    <?php
                }
                ?>
            </ul>
            <?php
            $content = OutputBuffering::get();
        } else {
            $content = (string)$this->ui->createMessage(t('No images found.'))
                    ->makeSlimLayout()
                    ->makeNotDismissable()
                    ->enableIcon()
                    ->makeInfo();
        }

        $this->sidebar->addHelp(
                sb()->t('Available images')->muted('(' . count($images) . ')'),
                $content
        );
    }

    protected function _renderContent(): UI_Renderable_Interface
    {
        if (empty($this->items)) {
            $this->renderer->appendContent(
                    $this->ui->createMessage(
                            t('No entries added.')
                    )
                            ->makeInfo()
                            ->enableIcon()
                            ->makeNotDismissable()
            );
        }

        return $this->renderer
                ->appendFormable($this)
                ->makeWithSidebar();
    }

    private function createSettingsForm(): void
    {
        $this->createFormableForm(self::FORM_NAME, $this->resolveDefaultValues());

        $this->addHiddenVar(AppVersion::REQUEST_PARAM_NUMBER, $this->version->getNumber());
        $this->addHiddenVar(AppVersion::REQUEST_PARAM_LANG_ID, $this->activeLanguageID);

        $this->addSection(t('Existing items'))
                ->setIcon(UI::icon()->list())
                ->collapse();

        foreach ($this->items as $item) {
            $this->injectItem($item);
        }

        $this->injectAddNew();

        if (empty($this->items)) {
            $this->setDefaultElement(self::KEY_NEW_CATEGORY);
        }
    }

    private function resolveDefaultValues(): array
    {
        $result = array();

        foreach ($this->items as $item) {
            $number = $item->getNumber();
            $result[self::elName($number, 'category')] = $item->getCategory()->getLabel();
            $result[self::elName($number, 'text')] = htmlspecialchars($item->getFormText());

            if ($this->activeLanguage->isDeveloperOnly()) {
                $result[self::elName($number, 'author')] = $item->getAuthor();
                $result[self::elName($number, 'issue')] = $item->getIssue();
            }
        }

        return $result;
    }

    private static function elName(int $number, string $element): string
    {
        return sprintf('item-%s-%s', $number, $element);
    }

    private function injectItem(CategoryItem $item): void
    {
        $number = $item->getNumber();

        $this->addElementAbstract(t('Item #%1$s', $number));

        $this->injectElementCategory(self::elName($number, 'category'));
        $this->injectElementText(self::elName($number, 'text'));

        if ($item->getLanguage()->isDeveloperOnly()) {
            $this->injectElementAuthor(self::elName($number, 'author'));
            $this->injectElementIssue(self::elName($number, 'issue'));
        }

        $this->addElementHTML('<hr>');
    }

    private function injectAddNew(): void
    {
        $header = $this->addSection(t('Create new entry'))
                ->setIcon(UI::icon()->add())
                ->setAbstract(t('Enter at least a category and text to add a new entry.'))
                ->collapse();

        if (empty($this->items)) {
            $header->expand();
        }

        $this->injectElementCategory(self::KEY_NEW_CATEGORY);
        $this->injectElementText(self::KEY_NEW_TEXT);

        if ($this->activeLanguage->isDeveloperOnly()) {
            $this->injectElementAuthor(self::KEY_NEW_AUTHOR);
            $this->injectElementIssue(self::KEY_NEW_ISSUE);
        }
    }

    private function injectElementCategory(string $name): void
    {
        $el = $this->addElementText($name, t('Category'));
        $el->addClass('input-xlarge');
        $el->addFilterTrim();
    }

    private function injectElementText(string $name): void
    {
        $el = $this->addElementTextarea($name, t('Text'));

        $el->addClass('input-xxlarge');
        $el->setRows(3);
        $el->addFilterTrim();
        $el->setComment(MarkdownRenderer::injectReference(null, true));
    }

    private function injectElementAuthor(string $name): void
    {
        $el = $this->addElementText($name, t('Author'));
        $el->addClass('input-large');
        $el->addFilterTrim();
    }

    private function injectElementIssue(string $name): void
    {
        $el = $this->addElementText($name, t('Issue'));
        $el->addClass('input-large');
        $el->addFilterTrim();
    }

    /**
     * @param array<string,string> $values
     * @return void
     */
    private function processForm(array $values): void
    {
        foreach ($this->items as $item) {
            $this->processFormItem($item, $values);
        }

        $this->processFormAdd($values);

        $this->whatsNew->writeToDisk();

        $this->redirectWithSuccessMessage(
                t(
                        'The entries for %1$s have been updated successfully at %2$s.',
                        $this->activeLanguageID,
                        sb()->time()
                ),
                $this->version->getAdminLanguageURL($this->activeLanguageID)
        );
    }

    /**
     * @param CategoryItem $item
     * @param array<string,string> $values
     * @return void
     */
    private function processFormItem(CategoryItem $item, array $values): void
    {
        $number = $item->getNumber();

        $item
                ->setText($values[self::elName($number, 'text')])
                ->setCategoryLabel($values[self::elName($number, 'category')]);

        if ($this->activeLanguage->isDeveloperOnly()) {
            $item
                    ->setAuthor($values[self::elName($number, 'author')])
                    ->setIssue($values[self::elName($number, 'issue')]);
        }
    }

    /**
     * @param array<string,string> $values
     * @return void
     */
    private function processFormAdd(array $values): void
    {
        if (empty($values[self::KEY_NEW_CATEGORY]) && empty($values[self::KEY_NEW_TEXT])) {
            return;
        }

        $this->activeLanguage->addItem(
                $values[self::KEY_NEW_CATEGORY],
                $values[self::KEY_NEW_TEXT],
                $values[self::KEY_NEW_AUTHOR] ?? '',
                $values[self::KEY_NEW_ISSUE] ?? ''
        );
    }

    private function resolveActiveLanguage() : void
    {
        $languages = VersionLanguage::getLanguageIDs();

        $lang = (string)Application_Driver::getInstance()
                ->getRequest()
                ->registerParam(AppVersion::REQUEST_PARAM_LANG_ID)
                ->setEnum($languages)
                ->get();

        if(empty($lang))
        {
            $lang = array_shift($languages);
        }

        $this->activeLanguageID = $lang;

        if(!$this->version->hasLanguage($lang))
        {
            $this->version->addLanguage($lang);
        }

        $this->activeLanguage = $this->version->getLanguage($lang);
    }

    private function resolveVersion(): void
    {
        $version = $this->whatsNew->getByRequest();

        if ($version === null) {
            $this->redirectWithErrorMessage(
                    t('Version number not found.'),
                    $this->whatsNew->getAdminListURL()
            );
        }

        $this->version = $version;
    }
}
