<?php

/**
 * @method UI_DataGrid_Action setIcon($icon)
 */
abstract class UI_DataGrid_Action implements Application_Interfaces_Iconizable, UI_Interfaces_Conditional
{
    use Application_Traits_Iconizable;
    use UI_Traits_Conditional;
    
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var CallableContainer
     */
    protected $callback;

    /**
     * @var UI_DataGrid
     */
    protected $grid;

    protected $attributes = array(
        'href' => 'javascript:void(0);'
    );

    protected $id;
    
   /**
    * @var UI
    */
    protected $ui;
    
    public function __construct(UI_DataGrid $grid, $name, $label)
    {
        $this->id = nextJSID();
        $this->grid = $grid;
        $this->name = $name;
        $this->label = $label;
        $this->ui = $grid->getUI();
        
        $this->restoreParams();
    }
    
    protected function restoreParams()
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
    
    protected $lockedParams = array();
    
    protected function lockParam($name)
    {
        if(!in_array($name, $this->lockedParams)) {
             $this->lockedParams[] = $name;
        }
    }
    
    protected function isParamLocked($name)
    {
        return in_array($name, $this->lockedParams);
    }
    
    public function getID()
    {
        return $this->id;
    }

    /**
     * Retrieves the identifying name of the action.
     * @return string
     */
    public function getName()
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
     * @param mixed[]|null $arguments Optional list of arguments to include in the callback.
     * @return UI_DataGrid_Action
     */
    public function setCallback($callback, $arguments=null)
    {
        if(empty($arguments)) {
            $arguments = array();
        } 
        
        $this->callback = new CallableContainer($callback, $arguments);
        
        return $this;
    }
    
   /**
    * Disables the "Select all entries" functionality 
    * for this list action.
    *  
    * @return UI_DataGrid_Action
    */
    public function disableSelectAll()
    {
        $this->selectAllDisabled = true;
        return $this;
    }
    
    protected $selectAllDisabled = false;
    
    public function isSelectAllEnabled()
    {
        return !$this->selectAllDisabled;
    }

    /**
     * Renders the markup for the action, to be included in the actions
     * drop down menu in the datagrid.
     *
     * @return string
     */
    public function render()
    {
        if(!$this->isValid())
        {
            return '';
        }

        $this->init();

        return
        '<li>'.
            '<a' . $this->renderAttributes() . '>' .
                $this->renderIcon() . $this->label .
            '</a>'.
        '</li>';
    }
    
    protected $classes = array();
    
    public function addClass($name)
    {
        if(!$this->hasClass($name)) {
            $this->classes[] = $name;
        }
        
        return $this;
    }
    
    public function hasClass($name)
    {
        return in_array($name, $this->classes);
    }
    
    public function makeDangerous()
    {
        return $this->addClass('action-danger');
    }
    
    public function makeSuccess()
    {
        return $this->addClass('action-success');
    }
    
    public function makeDeveloper()
    {
        $this->label = 'DEV: '.$this->label;
        return $this->addClass('action-developer');
    }

    protected function renderIcon()
    {
        if(isset($this->icon)) {
            return $this->icon->render() . ' ';
        }

        return '';
    }

    protected function renderAttributes()
    {
        $this->attributes['id'] = $this->getID();
        
        if(!empty($this->classes)) {
            $this->attributes['class'] = implode(' ', $this->classes);
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
    public function isLastBatch()
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
     * @return UI_DataGrid_Action
     */
    public function executeCallback($isLastBatch=false)
    {
        if (!isset($this->callback) || !$this->isValid())
        {
            return $this;
        }

        // the callback may trigger a redirect: in some cases
        // we want to intercept this, so we add the event handler 
        Application_EventHandler::addListener('Redirect', array($this, 'handle_redirect'));
        
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
            }
        }

        $this->callbackExecuted();
        
        return $this;
    }
    
    public function getSelectedValues()
    {
        return $this->grid->getSelected();
    }
    
    public function handle_redirect()
    {
        $this->callbackExecuted();
    }
    
    protected $callbackDone = false;
    
    protected function callbackExecuted()
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
    
    protected $jsMethod;
    
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
    * @return UI_DataGrid_Action
    */
    public function setJSMethod($methodName)
    {
    	$this->jsMethod = $methodName;
        return $this;
    }
 
    protected $tooltip;
    
   /**
    * Sets a tooltip to show when hovering over the action menu item.
    * @param string|int|UI_Renderable_Interface $text
    * @return UI_DataGrid_Action
    */
    public function setTooltip($text)
    {
        $this->tooltip = toString($text);
        return $this;
    }
    
    protected $confirmMessage;
    
   /**
    * Adds a confirmation message to the action: a message dialog will
    * be shown before the action is submitted.
    * 
    * @param string|int|UI_Renderable_Interface $message The confirmation message. HTML is allowed.
    * @param boolean $withInput Whether this is a critical message for which the user must type a confirmation string.
    * @return UI_DataGrid_Action
    */
    public function makeConfirm($message, $withInput=false)
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
    
    protected function init()
    {
        // can be extended as needed
    }
    
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
    * @param string $value
    * @return UI_DataGrid_Action
    */
    public function setParam($name, $value)
    {
        if(!$this->isParamLocked($name)) {
            $this->params[$name] = $value;
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
    public function getParam($name, $default=null)
    {
        if(isset($this->params[$name])) {
            return $this->params[$name];
        }
        
        return $default;
    }
    
    public function getParams()
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
