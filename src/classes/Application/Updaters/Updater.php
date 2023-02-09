<?php

use Application\AppFactory;
use AppUtils\OutputBuffering_Exception;

abstract class Application_Updaters_Updater  implements Application_Updaters_Interface
{
   /**
    * @var Application_Updaters
    */
	protected $updaters;

   /**
    * @var Application_Request
    */
	protected $request;

   /**
    * @var Application_Driver
    */
	protected $driver;
	
   /**
    * @var Application_Session
    */
	protected $session;
	
   /**
    * @var UI
    */
	protected $ui;
	
	protected static $simulationStarted = false;
	
   /**
    * @var Application
    */
	protected $app;
	
   /**
    * @var string
    */
	protected $sessionVar;
	
	public function __construct(Application_Updaters $updaters)
	{
		$this->updaters = $updaters;
		$this->request = Application_Request::getInstance();
		$this->driver = Application_Driver::getInstance();
		$this->app = $this->driver->getApplication();
		$this->session = Application::getSession();
		$this->sessionVar = 'Updater_'.$this->getID();
		$this->ui = $this->driver->getUI();
		
	    if($this->isSimulation() && !self::$simulationStarted) 
	    {
	        $logger = AppFactory::createLogger();
	        $logger->enableHTML();
	        $logger->logModeEcho();
	        
	        Application::logHeader('Simulation mode');
	        Application::log('Request parameters:');
	        Application::logData($_REQUEST);
	        
	        self::$simulationStarted = true;
	    }  	
	}

	protected $cachedID;
	
	public function getID()
	{
	    if(!isset($this->cachedID)) {
    		$this->cachedID = str_replace(APP_CLASS_NAME.'_Updaters_', '', get_class($this));
	    }
	    
	    return $this->cachedID;
	}

	public function buildURL($params=array())
	{
		$params['updater_id'] = $this->getID();
		return $this->updaters->buildURL($params);
	}

	protected function redirectTo($urlOrParams)
	{
		if(is_array($urlOrParams)) {
			$urlOrParams = $this->buildURL($urlOrParams);
		}
		
		$urlOrParams = str_replace('&amp;', '&', $urlOrParams);

		header('Location:'.$urlOrParams);
		Application::exit();
	}
	
	public function hasSpecificVersion($version)
	{
		$versions = $this->getValidVersions();
		if($versions=='*') {
		    return false;
		}
		
		if(!is_array($versions)) {
		    $versions = array($versions);
		}
		
		return in_array($version, $versions);
	}

    /**
     * Renders a page with the specified content and optional title.
     *
     * @param string|number|UI_Renderable_Interface $content
     * @param string|number|UI_Renderable_Interface $title
     * @return string
     * @throws Application_Exception
     * @throws OutputBuffering_Exception
     */
	protected function renderPage($content, $title='') : string
	{
		if(empty($title)) {
			$title = $this->getLabel();
		}
		
		return $this->updaters->renderPage($title, $content);
	}
	
    /**
     * Creates the markup for an error message and returns the generated HTML code.
     * Use the options array to set any desired options, see the {@link renderMessage()}
     * method for a list of options.
     *
     * @param string $message
     * @param array $options
     * @return string
     */
    public function renderErrorMessage($message, $options = array())
    {
        return $this->renderMessage($message, UI::MESSAGE_TYPE_ERROR, $options);
    }

    /**
     * Creates the markup for an informational message and returns the generated HTML code.
     * Use the options array to set any desired options, see the {@link renderMessage()}
     * method for a list of options.
     *
     * @param string $message
     * @param array $options
     * @return string
     */
    public function renderInfoMessage($message, $options = array())
    {
        return $this->renderMessage($message, UI::MESSAGE_TYPE_INFO, $options);
    }

    /**
     * Creates the markup for a success message and returns the generated HTML code.
     * Use the options array to set any desired options, see the {@link renderMessage()}
     * method for a list of options.
     *
     * @param string $message
     * @param array $options
     * @return string
     */
    public function renderSuccessMessage($message, $options = array())
    {
        return $this->renderMessage($message, UI::MESSAGE_TYPE_SUCCESS, $options);
    }

    /**
     * Creates the markup for a message of the specified type and returns the
     * generated HTML code. You may use the options array to configure the
     * error message further.
     *
     * Available option switches:
     *
     * - dismissable: boolean / Whether the message is dismissable. Default: true
     *
     * @param string $message
     * @param string $type
     * @param array $options
     * @return string
     */
    public function renderMessage($message, $type, $options = array())
    {
        if (!isset($options['dismissable'])) {
            $options['dismissable'] = true;
        }

        // add the missing dot if need be
        $message = trim($message);
        $lastChar = mb_substr($message, -1);
        switch ($lastChar) {
            case '>':
            case '.':
                break;

            default:
                $message .= '.';
        }

        $html = 
        '<div class="alert alert-'.$type.'">';
        	if($options['dismissable']) {
        		$html .=
        		'<button type="button" class="close" data-dismiss="alert">&times;</button>';
        	}
        	$html .=
        	$message.
        '</div>';

        return $html;
    }
    
   /**
    * Gets the value of a persistent updater-specific application setting.
    * 
    * @param string $name
    * @param string|NULL $default
    * @return string
    */
    public function getSetting(string $name, ?string $default=null) : ?string
    {
    	$name = $this->getSettingName($name);
    	return Application_Driver::createSettings()->get($name, $default);
    }
    
   /**
    * Sets the setting of a persistent updater-specific application setting.
    * 
    * @param string $name
    * @param string $value
    */
    protected function setSetting(string $name, string $value) : void
    {
    	$name = $this->getSettingName($name);
        Application_Driver::createSettings()->set($name, $value);
    }
    
   /**
    * Deletes an existing updater-specific application setting.
    * 
    * @param string $name
    */
    protected function deleteSetting(string $name) : void
    {
    	$name = $this->getSettingName($name);
        Application_Driver::createSettings()->delete($name);
    }
    
   /**
    * Resolves the name to use for the updater-specific application setting.
    * Makes the name unique to the updater by adding to updater's ID to it.
    * 
    * @param string $name
    * @return string
    */
    protected function getSettingName(string $name) : string
    {
    	return 'Updater_'.$this->getID().'_'.$name;
    }
    
   /**
    * Retrieves a session value of the updater.
    * 
    * @param string $name
    * @param string $default
    * @return string
    */
    protected function getSessionValue($name, $default=null)
    {
        $data = $this->session->getValue($this->sessionVar, array());
        if(isset($data['values']) && isset($data['values'][$name])) {
            return $data['values'][$name];
        }
        
        return $default;
    }
    
   /**
    * Sets a session value for the updater.
    * 
    * @param string $name
    * @param mixed $value
    */
    protected function setSessionValue($name, $value)
    {
        $data = $this->session->getValue($this->sessionVar, array());
        if(!isset($data['values'])) {
            $data['values'] = array();
        }
        
        $data['values'][$name] = $value;
        $this->session->setValue($this->sessionVar, $data);
    }
    
   /**
    * Handles cleanup operations once the update is done:
    * removes any remaining session variables and the like.
    */
    protected function cleanUp()
    {
        $this->session->unsetValue($this->sessionVar);
    }
    
    protected function isSimulation() : bool
    {
        return Application::isSimulation();
    }
    
   /**
    * Whether this updater script is currently enabled.
    * @return bool
    * @see Application_Updaters::isEnabled()
    */
    public function isEnabled()
    {
        return $this->updaters->isEnabled($this);
    }
    
    public function getCategory()
    {
        return t('%1$s system', $this->driver->getAppNameShort());
    }
    
    public function getListLabel()
    {
        return $this->getCategory().' - ' .$this->getLabel();
    }
    
    protected function startTransaction()
    {
        DBHelper::startTransaction();
    }
    
    protected function endTransaction()
    {
        if($this->isSimulation()) {
            DBHelper::rollbackTransaction();
        } else {
            DBHelper::commitTransaction();
        }
    }
    
    protected function rollbackTransaction()
    {
        DBHelper::rollbackTransaction();
    }
    
    protected function log($message)
    {
        Application::log(sprintf(
            'Updater %s | %s',
            $this->getID(),
            $message
        ));
    }
    
    protected function createSection()
    {
        return $this->ui->createPage('dummy')->createSection();
    }
}

interface Application_Updaters_Interface
{
	public function start() : string;
	public function getID();
	public function getLabel() : string;
	public function getCategory();
	public function getDescription();
	public function getValidVersions();
}