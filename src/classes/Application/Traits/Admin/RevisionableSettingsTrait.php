<?php

declare(strict_types=1);

use Application\Revisionable\RevisionableInterface;

/**
 * @property UI_Page_Sidebar $sidebar
 *
 */
trait RevisionableSettingsTrait
{
    protected string $formName;
    
    protected array $changed = array(
        'initialState' => null,
        'changes' => false,
        'structural' => false,
    );
    
    public function callback_beforeSave(Application_RevisionableCollection_DBRevisionable $revisionable) : void
    {
        $this->changed['changes'] = $revisionable->hasChanges();
        $this->changed['structural'] = $revisionable->hasStructuralChanges();
    }
    
    protected function hasChanges() : bool
    {
        return $this->changed['changes'];
    }
    
    protected function hasStructuralChanges() : bool
    {
        return $this->changed['structural'];
    }
    
    protected function stateChanged() : bool
    {
        return $this->revisionable->getStateName() !== $this->changed['initialState']->getName();
    }
    
    protected function _handleActions() : bool
    {
        $this->formName = $this->recordTypeName.'-settings';
        
        if($this->isEditMode()) {
            $this->changed['initialState'] = $this->revisionable->getState();
        }
        
        $this->createSettingsForm();
        
        if(!$this->isFormValid()) {
            return true;
        }
        
        $this->startTransaction();
        
            $revisionable = $this->processSettings($this->getFormValues());

        $this->endTransaction();
        
        if($this->isEditMode()) {
            if(!$this->hasChanges()) {
                $this->redirectWithInfoMessage(
                    t('The settings were not modified, no changes were made to the %1$s.', $this->collection->getRecordReadableNameSingular()),
                    $this->getBackOrCancelURL()
                );
            }
            
            $message = t(
                'The %1$s %2$s was updated successfully at %3$s.',
                $this->collection->getRecordReadableNameSingular(),
                $revisionable->getLabel(),
                date('H:i:s')
            );
            
            if($this->hasStructuralChanges() && $this->stateChanged()) {
                $message .= ' '.t(
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
                $this->collection->getRecordReadableNameSingular(),
                $revisionable->getLabel(),
                date('H:i:s')
            ),
            $this->getBackOrCancelURL()
        );
    }
    
    abstract protected function getBackOrCancelURL() : string;
    
    abstract protected function isEditMode() : bool;
    
    /**
     * Uses the submitted form values to create/update the
     * revisionable. Returns the revisionable instance.
     *
     * @param array $formValues
     * @return RevisionableInterface
     */
    abstract protected function processSettings(array $formValues) : RevisionableInterface;
    
    abstract protected function injectFormElements() : void;
    
    abstract protected function getDefaultFormData() : array;
    
    protected function createSettingsForm() : void
    {
        $this->createFormableForm($this->formName, $this->getDefaultFormData());
        
        $this->injectFormElements();
        
        $this->addFormablePageVars();
        
        if($this->isEditMode()) 
        {
            $this->addHiddenVar(
                $this->collection->getPrimaryKeyName(), 
                (string)$this->revisionable->getID()
            );
            
            if(!$this->revisionable->isEditable()) {
                $this->formableForm->makeReadonly();
            }
        }
    }
    
    protected function _handleSidebar() : void
    {
        if(!$this->isEditMode())
        {
            $btn = $this->sidebar->addButton('create_now', t('Create now'))
            ->makeClickableSubmit($this->formableForm)
            ->setIcon(UI::icon()->add())
            ->makePrimary();
        }
        else
        {
            $btn = $this->sidebar->addButton('save_now', t('Save now'))
            ->makeClickableSubmit($this->formableForm)
            ->setIcon(UI::icon()->save())
            ->makePrimary()
            ->requireChanging($this->revisionable)
            ->makeLockable();
        }
        
        $this->sidebar->addButton('cancel', t('Cancel'))
        ->makeLinked($this->getBackOrCancelURL());
        
        if(!$this->isEditMode()) {
            return;
        }
        
        $this->sidebar->addSeparator();
        
        $this->sidebar->addRevisionableStateInfo($this->revisionable);
        
        if(!$this->user->isDeveloper() || !$this->revisionable->isChangingAllowed()) {
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
    
    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendFormable($this)
            ->makeWithSidebar();
    }
}
