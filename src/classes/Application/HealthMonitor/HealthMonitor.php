<?php
/**
 * File containing the {@link Application_HealthMonitor} class.
 * @package Application
 * @subpackage HealthMonitor
 */

use AppUtils\ClassHelper;
use AppUtils\XMLHelper;

/**
 * The health monitor checks all SPIN systems to make sure everything
 * is running as it should, and offers the possibility to serve detailed
 * system health status information in XML form, as used by the global
 * shop monitoring.
 *
 * Note: Each checkable SPIN component has its own class.
 *
 * @package Application
 * @subpackage HealthMonitor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_HealthMonitor
{
    /**
     * @var Application_Driver
     */
    private $driver;
    
    private $stateWeights;
    
    public function __construct()
    {
        $this->driver = Application_Driver::getInstance();
        $this->loadComponents();
    
        $this->stateWeights = array(
            Application_HealthMonitor_Component::STATE_OK => 1,
            Application_HealthMonitor_Component::STATE_WARNING => 2,
            Application_HealthMonitor_Component::STATE_ERROR => 3,
        );
    }
    
    protected $globalState;
    
   /**
    * @var Application_HealthMonitor_Component[]
    */
    protected array $components = array();
    
    protected function loadComponents() : void
    {
        $paths = array(
            $this->driver->getClassesFolder().'/HealthMonitor' => APP_CLASS_NAME.'_HealthMonitor',
            $this->driver->getApplication()->getClassesFolder().'/Application/HealthMonitor/Component' => 'Application_HealthMonitor_Component'
        );
        
        foreach($paths as $path => $baseClassName) 
        {
            if(!is_dir($path)) {
                continue;
            }
            
            $ids = AppUtils\FileHelper::createFileFinder($path)
            ->getPHPClassNames();

            foreach($ids as $id) {
                if(!$this->hasComponent($id)) {
                    $this->createComponent($id, $baseClassName);
                }
            }
        }
    }
    
    protected function hasComponent($componentID)
    {
        return isset($this->components[$componentID]);
    }
    
    protected function createComponent($componentID, $baseClassName) : Application_HealthMonitor_Component
    {
        if (isset($this->components[$componentID])) {
            return $this->components[$componentID];
        }
    
        $className = ClassHelper::requireResolvedClass($baseClassName.'_' . $componentID);

        $this->components[$componentID] = ClassHelper::requireObjectInstanceOf(
            Application_HealthMonitor_Component::class,
            new $className()
        );
    
        return $this->components[$componentID];
    }
    
    protected $globalErrors = 0;
    
    protected $globalWarnings = 0;
    
    /**
     * Loads an queries all health monitor components,
     * so that they have all collected the required
     * information and all you need is to iterate over
     * them to access the results.
     *
     * Also populates the global statistics.
     */
    protected function queryComponents()
    {
        /* @var $component Application_HealthMonitor_Component */
    
        $worstState = $this->stateWeights[Application_HealthMonitor_Component::STATE_OK];
        foreach ($this->components as $component) {
            $component->collectData();
            $state = $component->getState();
            $weight = $this->stateWeights[$state];
            if ($weight > $worstState) {
                $worstState = $weight;
            }
    
            if ($component->isError()) {
                $this->globalErrors++;
            } else {
                if ($component->isWarning()) {
                    $this->globalWarnings++;
                }
            }
        }
    
        $reverseWeights = array_flip($this->stateWeights);
        $this->globalState = $reverseWeights[$worstState];
    }
    
    public function serveContent()
    {
        $request = $this->driver->getRequest();
        $accepts = $request->getAcceptHeaders();
        $accept = array_shift($accepts);
    
        if ($accept == 'text/xml') 
        {
            $this->serveXML();
            return;
        } 
        
        $format = 'html';
        if(isset($_REQUEST['format']) && in_array($_REQUEST['format'], array('text', 'xml', 'html'))) {
            $format = $_REQUEST['format'];
        }
        
        switch ($format) {
            case 'text':
                $this->serveText();
                break;
                
            case 'xml':
                $this->serveXML();
                break;

            case 'html':
                $this->serveHTML();
                break;
        }
    }
    
    public function getGlobalState()
    {
        return $this->globalState;
    }
    
    public function getStateDefs()
    {
        return array(
            Application_HealthMonitor_Component::STATE_OK => '<span class="label label-success">OK</span>',
            Application_HealthMonitor_Component::STATE_WARNING => '<span class="label label-warning">WARNING</span>',
            Application_HealthMonitor_Component::STATE_ERROR => '<span class="label label-important">ERROR</span>'
        );
    }
    
    public function countErrors()
    {
        return $this->globalErrors;
    }
    
    public function countWarnings()
    {
        return $this->globalWarnings;
    }
    
   /**
    * @return Application_HealthMonitor_Component[]
    */
    public function getComponents()
    {
        return $this->components;
    }
    
    public function serveHTML()
    {
        $this->queryComponents();
        
        displayHTML(
            UI::getInstance()
            ->createPage('healthmonitor')
            ->renderTemplate(
                'health-monitor', 
                array(
                    'monitor' => $this,
                )
            )
        );
    }
    
    public function serveText()
    {
        $this->queryComponents();
    
        $lines = array();
        $lines[] = sprintf(
            'SUMMARY: %1$s -- %2$s, WARNING %3$s, ERROR %4$s',
            $this->globalState,
            $this->globalState,
            $this->globalWarnings,
            $this->globalErrors
        );
    
        foreach ($this->components as $component) {
            $duration = $component->getDuration();
            if (empty($duration)) {
                $duration = '0';
            }
    
            $line = sprintf(
                '%1$s: %2$s -- duration [%3$s ms]',
                $component->getName(),
                $component->getState(),
                $duration
            );
    
            $yp = $component->getYellowPagesURL();
            if (!empty($yp)) {
                $line .= sprintf(' [%1$s]', $yp);
            }
    
            $lines[] = $line;
        }
    
        displayTXT(implode(PHP_EOL, $lines));
    }
    
    public function serveXML()
    {
        $this->queryComponents();
    
        $xml = XMLHelper::create();
        $rootNode = $xml->createRoot('testSummary');
        $xml->addTextTag($rootNode, 'state', $this->globalState);
    
        $resultsNode = $xml->addTag($rootNode, 'results');
        foreach ($this->components as $component) {
            $testNode = $xml->addTag($resultsNode, 'test');
            $xml->addTextTag($testNode, 'name', $component->getName());
            $xml->addTextTag($testNode, 'description', $component->getDescription());
            $xml->addTextTag($testNode, 'severity', $component->getSeverity());
            $xml->addTextTag($testNode, 'yellowPagesURL', $component->getYellowPagesURL());
    
            $resultNode = $xml->addTag($testNode, 'result');
            $xml->addTextTag($resultNode, 'state', $component->getState());
            if ($component->hasDuration()) {
                $xml->addTextTag($resultNode, 'duration', $component->getDuration());
            }
            if ($component->hasMessage()) {
                $xml->addTextTag($resultNode, 'message', $component->getMessage());
            }
            if ($component->hasException()) {
                $xml->addTextTag($resultNode, 'exception', $component->getException());
            }
        }
    
        displayXML($xml->saveXML());
    }
}
