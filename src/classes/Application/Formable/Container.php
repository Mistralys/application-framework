<?php
/**
 * @package Application
 * @subpackage Formable
 */

declare(strict_types=1);

/**
 * Abstract class that can be extended to use an existing
 * formable instance natively in the extended class.
 * 
 * > NOTE: This formable will only be usable once the parent
 * > formable has been initialized.
 *
 * @package Application
 * @subpackage Formable
 * @see Application_Formable_Container
 */
abstract class Application_Formable_Container extends Application_Formable
{
    public const int ERROR_INITIALIZATION_ERROR = 36901;
    
    protected ?Application_Interfaces_Formable $originFormable = null;

    public function __construct(Application_Interfaces_Formable $formable)
    {
        $this->switchFormable($formable);

        if($this->originFormable === null) {
            throw new Application_Formable_Exception(
                'Initialization error.',
                sprintf(
                    'The origin formable is not set after calling [%s()].',
                    array($this, 'switchFormable')[1]
                ),
                self::ERROR_INITIALIZATION_ERROR
            );
        }
        
        $this->initContainer();
    }

    protected function initContainer() : void
    {
        
    }
    
   /**
    * Called when the parent formable has been initialized:
    * cascades the initialization to this container and all
    * child containers, if any.
    */
    public function handleFormableInitialized() : void
    {
        $this->initFormable(
            $this->originFormable->getFormInstance(),
            $this->originFormable->getFormableDefaultContainer()
        );
    }
    
    public function switchFormable(Application_Interfaces_Formable $formable) : self
    {
        $this->logFormable('Switching to: '.$formable->getFormableIdentification());
        
        if(isset($this->originFormable)) {
            $this->originFormable->removeContainer($this);
        }
        
        $this->originFormable = $formable;
        
        $formable->registerContainer($this);
        
        if($formable->isInitialized() && !$this->isInitialized()) {
            throw new Application_Formable_Exception(
                'Initialization error.',
                '',
                self::ERROR_INITIALIZATION_ERROR
            );
        }

        return $this;
    }

    public function render() : string
    {
        return $this->renderFormable();
    }
}
