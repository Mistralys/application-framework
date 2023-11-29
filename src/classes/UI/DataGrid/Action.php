<?php

use AppUtils\Interface_Classable;
use AppUtils\NamedClosure;
use AppUtils\OutputBuffering;
use AppUtils\OutputBuffering_Exception;
use AppUtils\Traits_Classable;

abstract class UI_DataGrid_Action
    implements
    Application_Interfaces_Iconizable,
    UI_Interfaces_Conditional,
    UI_Renderable_Interface,
    Interface_Classable
{
    use Application_Traits_Iconizable;
    use UI_Traits_RenderableGeneric;
    use UI_Traits_Conditional;
    use Traits_Classable;
    
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var CallableContainer|NULL
     */
    protected $callback;

    /**
     * @var UI_DataGrid
     */
    protected $grid;

    /**
     * @var array<string,string>
     */
    protected $attributes = array(
        'href' => 'javascript:void(0);'
    );

    /**
     * @var string
     */
    protected $id;
    
   /**
    * @var UI
    */
    protected $ui;

    /**
     * @param UI_DataGrid $grid
     * @param string $name
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @throws UI_Exception
     */
    public function __construct(UI_DataGrid $grid, string $name, $label)
    {
        $this->id = nextJSID();
        $this->grid = $grid;
        $this->name = $name;
        $this->label = toString($label);
        $this->ui = $grid->getUI();
        
        $this->restoreParams();
    }

    public function getUI() : UI
    {
        return $this->ui;
    }

    protected function restoreParams() : void
    {
        $request = Application_Request::getInstance();
        
        $params = $request->registerParam('action_'.$this->getName())->setArray()->get();
        if(!$params) {
            return;
        }
        
        foreach($params as $name => $value) {
            $this->setParam($name, $value);
            $this->lockParam($name);
        }
    }

    /**
     * @var string[]
     */
    protected $lockedParams = array();
    
    protected function lockParam(string $name) : void
    {
        if(!in_array($name, $this->lockedParams)) {
             $this->lockedParams[] = $name;
        }
    }
    
    protected function isParamLocked(string $name) : bool
    {
        return in_array($name, $this->lockedParams);
    }
    
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Retrieves the identifying name of the action.
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Allows automating acting on a submitted list action: the callback
     * will be called if the user selects this action. 
     * 
     * The callback gets the following parameters:
     * 
     * - This list action object instance
     * - The selected item IDs
     * - [Optional arguments]
     *
     * @param callable $callback The callback to use.
     * @param array $arguments Optional list of arguments to include in the callback.
     * @return $this
     */
    public function setCallback(callable $callback, array $arguments=array())
    {
        $this->callback = new CallableContainer($callback, $arguments);
        
        return $this;
    }
    
   /**
    * Disables the "Select all entries" functionality 
    * for this list action.
    *  
    * @return $this
    */
    public function disableSelectAll()
    {
        $this->selectAllDisabled = true;
        return $this;
    }

    /**
     * @var bool
     */
    protected $selectAllDisabled = false;
    
    public function isSelectAllEnabled() : bool
    {
        return !$this->selectAllDisabled;
    }

    /**
     * Renders the markup for the action, to be included in the actions
     * drop down menu in the datagrid.
     *
     * @return string
     * @throws OutputBuffering_Exception
     */
    public function render() : string
    {
        if(!$this->isValid())
        {
            return '';
        }

        $this->init();

        OutputBuffering::start();

        ?>
        <li>
            <a <?php echo $this->renderAttributes() ?>>
                <?php echo sb()
                    ->add((string)$this->getIcon())
                    ->add($this->label);
                ?>
            </a>
        </li>
        <?php

        return OutputBuffering::get();
    }

    /**
     * @return $this
     */
    public function makeDangerous()
    {
        return $this->addClass('action-danger');
    }

    /**
     * @return $this
     */
    public function makeSuccess()
    {
        return $this->addClass('action-success');
    }

    /**
     * @return $this
     */
    public function makeDeveloper()
    {
        $this->label = 'DEV: '.$this->label;
        return $this->addClass('action-developer');
    }

    protected function renderAttributes() : string
    {
        $this->attributes['id'] = $this->getID();
        
        if(!empty($this->classes)) {
            $this->attributes['class'] = $this->classesToString();
        }
        
        if(isset($this->jsMethod)) {
        	$this->attributes['onclick'] = sprintf(
        	    '%s(%s.GetSelectedEntries(), %s)',
        	    $this->jsMethod,
        	    $this->grid->getClientObjectName(),
        	    $this->grid->getClientObjectName()
        	);
        }
        
        if(!$this->isSelectAllEnabled()) {
            $this->attributes['onclick'] = sprintf(
                'if(%s.IsSelectAllActive()) { %s.DialogActionNotSelectAllEnabled(); } else {'.$this->attributes['onclick'].';}',
                $this->grid->getClientObjectName(),
                $this->grid->getClientObjectName()
            );
        }
        
        if(isset($this->confirmMessage)) 
        {
            $varName = 'gcm'.nextJSID();
            $funcName = 'gcf'.nextJSID();
            $this->ui->addJavascriptHeadVariable('var '.$varName, $this->confirmMessage['message']);
            $this->ui->addJavascriptHead('function '.$funcName.'() {'.$this->attributes['onclick'].';}');
            
            $dialog ='application.createConfirmationDialog('.$varName.', '.$funcName.')';
            if($this->hasClass('action-danger')) {
                $dialog .= '.MakeDangerous()';
            }
            
            if($this->confirmMessage['withInput']) {
                $dialog .= '.MakeWithInput()';
            }
            
            $this->attributes['onclick'] = $dialog.'.Show()';
        }
        
        if(isset($this->tooltip)) {
            $this->attributes['title'] = $this->tooltip;
            JSHelper::tooltipify($this->getID());
        }
        
        $items = array();
        foreach ($this->attributes as $name => $value) {
            $items[] = $name . '="' . $value . '"';
        }

        return ' ' . implode(' ', $items);
    }

    /**
     * @var bool
     */
    protected $lastBatch = false;
    
   /**
    * Checks whether this is the last batch of actions to
    * execute when the user selected all entries in a 
    * datagrid.
    * 
    * Note: this always returns true when not in select all mode.
    * 
    * @return boolean
    */
    public function isLastBatch() : bool
    {
        if($this->isSelectAllEnabled()) {
            return $this->lastBatch;
        }
        
        return true;
    }

    /**
     * Executes this action's callback, if any. If no
     * callback has been set using the {@link setCallback()}
     * method, this will not do anything.
     * 
     * @param bool $isLastBatch
     * @return $this
     */
    public function executeCallback(bool $isLastBatch=false)
    {
        if (!isset($this->callback) || !$this->isValid())
        {
            return $this;
        }

        // the callback may trigger a redirect: in some cases
        // we want to intercept this, so we add the event handler 
        Application::addRedirectListener(NamedClosure::fromClosure(
            Closure::fromCallable(array($this, 'callback_redirect')),
            array($this, 'callback_redirect')
        ));
        
        try
        {
            $this->lastBatch = $isLastBatch;
            $this->callback->call(array($this, $this->getSelectedValues()));
            $this->lastBatch = false;
        } 
        catch(Exception $e) 
        {
            if($this->grid->isAjax()) {
                $json = Application_AjaxMethod::formatJSONException($e);
                Application_Request::sendJSON($json);
            } else {
                throw $e;
            }
        }

        $this->callbackExecuted();
        
        return $this;
    }
    
    public function getSelectedValues() : array
    {
        return $this->grid->getSelected();
    }

    private function callback_redirect() : void
    {
        $this->callbackExecuted();
    }

    /**
     * @var bool
     */
    protected $callbackDone = false;
    
    protected function callbackExecuted() : void
    {
        if($this->callbackDone) {
            return;
        }
        
        $this->callbackDone = true;
        
        if(!$this->grid->isAjax()) {
            return;
        }
     
        $ui = UI::getInstance();
        $messages = $ui->getMessages();
        $ui->clearMessages();
        
        $data = array(
            'messages' => $messages
        );
        
        $response = Application_AjaxMethod::formatJSONResponse($data);
        Application_Request::sendJSON($response);
    }

    /**
     * @var string|NULL
     */
    protected $jsMethod = null;
    
   /**
    * Sets a javascript method to call when the link is clicked.
    * Note: the action does not get submitted serverside anymore,
    * it must be handled entirely clientside. 
    *
    * The specified method gets two parameters: 
    * 
    * - An indexed array with all selected list entries.
    * - The datagrid object instance
    *
    * @param string $methodName Only the method name, e.g. "DoSomething".
    * @return $this
    */
    public function setJSMethod(string $methodName)
    {
    	$this->jsMethod = $methodName;
        return $this;
    }

    /**
     * @var string|NULL
     */
    protected $tooltip = null;

    /**
     * Sets a tooltip to show when hovering over the action menu item.
     * @param string|int|UI_Renderable_Interface $text
     * @return $this
     * @throws UI_Exception
     */
    public function setTooltip($text)
    {
        $this->tooltip = toString($text);
        return $this;
    }

    /**
     * @var array{message:string,withInput:bool}|NULL
     */
    protected $confirmMessage = null;

    /**
     * Adds a confirmation message to the action: a message dialog will
     * be shown before the action is submitted.
     *
     * @param string|int|UI_Renderable_Interface $message The confirmation message. HTML is allowed.
     * @param boolean $withInput Whether this is a critical message for which the user must type a confirmation string.
     * @return $this
     * @throws UI_Exception
     */
    public function makeConfirm($message, bool $withInput=false)
    {
        $this->confirmMessage = array(
            'message' => toString($message),
            'withInput' => $withInput 
        );
        
        if($withInput) {
            $this->makeDangerous();
        }
        
        return $this;
    }
    
    protected function init() : void
    {
        // can be extended as needed
    }

    /**
     * @var array<string,string>
     */
    protected $params = array();

    /**
     * Sets a freeform parameter: these can be used to
     * store data that the callback function can use.
     * It has no functionality beyond storing data.
     *
     * NOTE: The value must be convertible to a string.
     * When using the select all feature, the parameters
     * are passed on via AJAX.
     *
     * @param string $name
     * @param string|number|UI_Renderable_Interface $value
     * @return $this
     * @throws UI_Exception
     */
    public function setParam(string $name, $value)
    {
        if(!$this->isParamLocked($name)) {
            $this->params[$name] = toString($value);
        }
        
        return $this;
    }
    
   /**
    * Retrieves a previously added parameter, if any.
    * 
    * @param string $name
    * @param string $default
    * @return string
    */
    public function getParam(string $name, string $default='') : string
    {
        if(isset($this->params[$name])) {
            return $this->params[$name];
        }
        
        return $default;
    }

    /**
     * @return array<string,string>
     */
    public function getParams() : array
    {
        return $this->params;
    }
    
   /**
    * Creates a configurable redirect message for the specified
    * amount of affected records: determines the message that needs
    * to be added, and redirects to the target URL. 
    * 
    * @param string $redirectURL
    * @return UI_DataGrid_RedirectMessage
    */
    public function createRedirectMessage(string $redirectURL) : UI_DataGrid_RedirectMessage
    {
        return new UI_DataGrid_RedirectMessage($this, $redirectURL);
    }
}
