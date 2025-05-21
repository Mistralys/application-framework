<?php
/**
 * @package Tagging
 * @subpackage Admin Screens
 */

declare(strict_types=1);

namespace Application\Tags\AdminScreens;

use Application\AppFactory;
use Application\Tags\Taggables\Taggable;
use Application\Tags\Taggables\TaggableInterface;
use UI;
use UI_Themes_Theme_ContentRenderer;

/**
 * Trait used to implement a screen for tagging records.
 * The record must implement the {@see TaggableInterface} interface.
 *
 * Usage:
 *
 * 1. Implement the interface {@see RecordTaggingScreenInterface}
 * 2. Use this trait.
 * 3. Call the method {@see handleTaggableActions()} in the action method.
 *
 * @package Tagging
 * @subpackage Admin Screens
 * @see RecordTaggingScreenInterface
 */
trait RecordTaggingScreenTrait
{
    private bool $tagsAvailable = false;

    protected function handleTaggableActions() : void
    {
        $this->tagsAvailable = $this->getTaggableRecord()->getTagCollection()->hasAvailableTags();

        if(!$this->tagsAvailable) {
            return;
        }

        $this->createTagForm();

        if($this->request->getBool(self::REQUEST_VAR_CLEAR_ALL)) {
            $this->handleClearAll();
        }

        if($this->isFormValid())
        {
            $formValues = $this->getFormValues();
            $this->handleSaveTags((array)$formValues[RecordTaggingScreenInterface::SETTING_TAGS]);
        }
    }

    protected function handleClearAll() : void
    {
        $manager = $this->getTagManager();

        $this->startTransaction();

        $manager->removeAll();

        $this->endTransaction();

        $this->redirectWithSuccessMessage(
            t('The tag selection has been cleared at %1$s.', sb()->time()),
            $this->getAdminSuccessURL()
        );
    }

    /**
     * @param string[] $tagIDs
     * @return void
     */
    public function handleSaveTags(array $tagIDs) : void
    {
        $manager = $this->getTagManager();
        $collection = AppFactory::createTags();

        $this->startTransaction();

        $manager->removeAll();

        foreach($tagIDs as $tagID) {
            $manager->addTag($collection->getByID((int)$tagID));
        }

        $this->endTransaction();

        $this->redirectWithSuccessMessage(
            t('The tag selection has been saved at %1$s.', sb()->time()),
            $this->getAdminSuccessURL()
        );
    }

    public function _handleSidebar() : void
    {
        if(!$this->tagsAvailable) {
            return;
        }

        $this->sidebar->addButton('save-tags', t('Save now'))
            ->makePrimary()
            ->setIcon(UI::icon()->save())
            ->makeClickableSubmit($this);

        $this->sidebar->addButton('cancel', t('Cancel'))
            ->link($this->getAdminCancelURL());

        $this->sidebar->addSeparator();

        $this->sidebar->addButton('remove-all', t('Clear all'))
            ->setTooltip(t('Removes all tags from the selection'))
            ->makeConfirm(t('Are you sure you want to remove all tags?'))
            ->makeDangerous()
            ->setIcon(UI::icon()->delete())
            ->link($this->getAdminClearAllURL());
    }

    public function getAdminClearAllURL() : string
    {
        return (string)$this->getTaggableRecord()->adminURLTagging()->string(self::REQUEST_VAR_CLEAR_ALL, 'yes');
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        if(!$this->tagsAvailable) {
            return $this->renderer
                ->appendContent($this->renderNoTagsAvailable())
                ->makeWithoutSidebar();
        }

        return $this->renderer
            ->appendFormable($this)
            ->makeWithSidebar();
    }

    private function renderNoTagsAvailable() : string
    {
        return (string)$this->getUI()->createMessage(sb()
            ->t('No tags are available for this record type.')
        )
            ->makeInfo()
            ->enableIcon()
            ->makeNotDismissable();
    }

    private function getDefaultFormData() : array
    {
        return array(
            RecordTaggingScreenInterface::SETTING_TAGS => $this->getTagManager()->getTagIDs()
        );
    }

    private function createTagForm() : void
    {
        $record = $this->getTaggableRecord();

        $this->createFormableForm(self::FORM_NAME, $this->getDefaultFormData());
        $this->addFormablePageVars();
        $this->addHiddenVar(
            $record->getTagConnector()->getPrimaryName(),
            (string)$record->getTagRecordPrimaryValue()
        );

        $this->_handleHiddenFormVars();

        $this->injectTagTree();
    }

    /**
     * Overridable method to allow the screen to add any hidden
     * form variables that may be required.
     *
     * @return void
     */
    protected function _handleHiddenFormVars() : void
    {

    }

    public function getTagManager() : Taggable
    {
        return $this->getTaggableRecord()->getTagManager();
    }

    public function getAdminSuccessURL(): string
    {
        return (string)$this->getTaggableRecord()->adminURLTagging();
    }

    protected function injectTagTree() : void
    {
        $el = $this->getTagManager()->injectTagTree($this, RecordTaggingScreenInterface::SETTING_TAGS, t('Tags'));

        $el->getTree()->setShowRoot(false);

        $this->makeStandalone($el);
    }
}
