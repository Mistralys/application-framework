<?php
/**
 * File containing the {@link Application_Formable_Container} class.
 * 
 * @package Application
 * @subpackage Formable
 * @see Application_Formable_Container
 */

/**
 * Abstract class that can be extended to use an existing
 * formable instance natively in the extended class.
 * 
 * NOTE: This formable will only be useable once the parent
 * formable has been initialized.
 *
 * @package Application
 * @subpackage Formable
 * @see Application_Formable_Container
 */
abstract class Application_Formable_Container extends Application_Formable
{
    public const ERROR_INITIALIZATION_ERROR = 36901;
    
   /**
    * @var Application_Interfaces_Formable
    */
    protected $originFormable;

    public function __construct(Application_Interfaces_Formable $formable)
    {
        $this->switchFormable($formable);
        
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
    public function handleFormableInitialized()
    {
        $this->initFormable(
            $this->originFormable->getFormInstance(),
            $this->originFormable->getFormableContainer()
        );
    }
    
    public function switchFormable(Application_Interfaces_Formable $formable)
    {
        $this->logFormable('Switching to: '.$formable->getFormableIdentification());
        
        if(isset($this->originFormable)) {
            $this->originFormable->removeContainer($this);
        }
        
        $this->originFormable = $formable;
        
        $formable->registerContainer($this);
        
        if($formable->isInitialized() && !$this->isInitialized()) {
            throw new Application_Exception(
                'Initialization error.',
                '',
                self::ERROR_INITIALIZATION_ERROR
            );
        }
    }

    public function render() : string
    {
        return $this->renderFormable();
    }
}
