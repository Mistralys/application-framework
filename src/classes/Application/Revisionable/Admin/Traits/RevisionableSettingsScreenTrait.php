<?php

declare(strict_types=1);

namespace Application\Revisionable\Admin\Traits;

use Application\Revisionable\RevisionableInterface;
use UI;
use UI_Page_Sidebar;
use UI_Themes_Theme_ContentRenderer;

/**
 * @property UI_Page_Sidebar $sidebar
 * @see RevisionableSettingsScreenInterface
 */
trait RevisionableSettingsScreenTrait
{
    public const string KEY_CHANGES = 'changes';
    public const string KEY_STRUCTURAL = 'structural';
    public const string KEY_INITIAL_STATE = 'initialState';

    protected string $formName;

    protected array $changed = array(
        self::KEY_INITIAL_STATE => null,
        self::KEY_CHANGES => false,
        self::KEY_STRUCTURAL => false,
    );

    public function callback_beforeSave(RevisionableInterface $revisionable): void
    {
        $this->changed[self::KEY_CHANGES] = $revisionable->hasChanges();
        $this->changed[self::KEY_STRUCTURAL] = $revisionable->hasStructuralChanges();
    }

    protected function hasChanges(): bool
    {
        return $this->changed[self::KEY_CHANGES];
    }

    protected function hasStructuralChanges(): bool
    {
        return $this->changed[self::KEY_STRUCTURAL];
    }

    protected function stateChanged(): bool
    {
        return $this->requireRevisionable()->getStateName() !== $this->changed[self::KEY_INITIAL_STATE]->getName();
    }

    protected function _handleActions(): bool
    {
        $this->formName = $this->createCollection()->getRecordTypeName() . '-settings';

        $collection = $this->createCollection();
        $revisionable = $this->getRevisionableOrRedirect();

        if ($this->isEditMode()) {
            $this->changed[self::KEY_INITIAL_STATE] = $revisionable->getState();
        }

        $this->createSettingsForm();

        if (!$this->isFormValid()) {
            return true;
        }

        $this->startTransaction();

        $this->processSettings($this->getFormValues());

        $this->endTransaction();

        if ($this->isEditMode()) {
            if (!$this->hasChanges()) {
                $this->redirectWithInfoMessage(
                    t('The settings were not modified, no changes were made to the %1$s.', $collection->getRecordReadableNameSingular()),
                    $this->getBackOrCancelURL()
                );
            }

            $message = t(
                'The %1$s %2$s was updated successfully at %3$s.',
                $collection->getRecordReadableNameSingular(),
                $revisionable->getLabel(),
                date('H:i:s')
            );

            if ($this->hasStructuralChanges() && $this->stateChanged()) {
                $message .= ' ' . t(
                        'Because of structural changes, the state has been changed to %1$s.',
                        $revisionable->getCurrentPrettyStateLabel()
                    );
            }

            $this->redirectWithSuccessMessage(
                $message,
                $this->getBackOrCancelURL()
            );
        }

        $this->redirectWithSuccessMessage(
            t(
                'The %1$s %2$s was created successfully at %3$s.',
                $collection->getRecordReadableNameSingular(),
                $revisionable->getLabel(),
                date('H:i:s')
            ),
            $this->getBackOrCancelURL()
        );
    }

    abstract protected function getBackOrCancelURL(): string;

    abstract protected function isEditMode(): bool;

    /**
     * Uses the submitted form values to create/update the
     * revisionable. Returns the revisionable instance.
     *
     * @param array<string,mixed> $formValues
     * @return RevisionableInterface
     */
    abstract protected function processSettings(array $formValues): RevisionableInterface;

    abstract protected function injectFormElements(): void;

    abstract protected function getDefaultFormData(): array;

    protected function createSettingsForm(): void
    {
        $revisionable = $this->requireRevisionable();

        $this->createFormableForm($this->formName, $this->getDefaultFormData());

        $this->injectFormElements();

        $this->addFormablePageVars();

        if ($this->isEditMode()) {
            $this->addHiddenVar(
                $this->createCollection()->getRecordPrimaryName(),
                (string)$revisionable->getID()
            );

            if (!$revisionable->isEditable()) {
                $this->formableForm->makeReadonly();
            }
        }
    }

    protected function _handleSidebar(): void
    {
        $revisionable = $this->requireRevisionable();

        if (!$this->isEditMode()) {
            $btn = $this->sidebar->addButton('create_now', t('Create now'))
                ->makeClickableSubmit($this->formableForm)
                ->setIcon(UI::icon()->add())
                ->makePrimary();
        } else {
            $btn = $this->sidebar->addButton('save_now', t('Save now'))
                ->makeClickableSubmit($this->formableForm)
                ->setIcon(UI::icon()->save())
                ->makePrimary()
                ->requireChanging($revisionable)
                ->makeLockable();
        }

        $this->sidebar->addButton('cancel', t('Cancel'))
            ->makeLinked($this->getBackOrCancelURL());

        if (!$this->isEditMode()) {
            return;
        }

        $this->sidebar->addSeparator();

        $this->sidebar->addRevisionableStateInfo($revisionable);

        if (!$this->user->isDeveloper() || !$revisionable->isChangingAllowed()) {
            return;
        }

        $this->sidebar->addSeparator();

        $panel = $this->sidebar->addDeveloperPanel();
        $panel->addButton(
            UI::button($btn->getLabel())
                ->setIcon(clone $btn->getIcon())
                ->click($this->formableForm->getJSSubmitHandler(true))
        );
    }

    protected function _renderContent(): UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendFormable($this)
            ->makeWithSidebar();
    }
}
