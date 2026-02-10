<?php
/**
 * @package Application
 * @subpackage HealthMonitor
 */

/**
 * Base class skeleton for SPIN components that can be checked using
 * the health monitor.
 *
 * @package Application
 * @subpackage HealthMonitor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_HealthMonitor
 */
abstract class Application_HealthMonitor_Component
{
    /**
     * The Application is completely not usable
     * @var string
     */
    public const string SEVERITY_BLOCKER = 'BLOCKER';

    /**
     * Most features are not usable
     * @var string
     */
    const SEVERITY_CRITICAL = 'CRITICAL';

    /**
     * Some features are not useable
     * @var string
     */
    const SEVERITY_MAJOR = 'MAJOR';

    /**
     * Application\Application can handle with the failure
     * @var string
     */
    const SEVERITY_MINOR = 'MINOR';

    const STATE_OK = 'OK';

    const STATE_WARNING = 'WARNING';

    const STATE_ERROR = 'ERROR';

    abstract public function getName();

    abstract public function getDescription();

    abstract public function getYellowPagesURL();

    abstract public function getSeverity();

    abstract public function collectData();

    protected $state = self::STATE_OK;

   /**
    * @var Application_Driver
    */
    protected $driver;
    
    public function __construct()
    {
    	$this->driver = Application_Driver::getInstance();
    }
    
    public function getState()
    {
        return $this->state;
    }

    protected $duration = null;

    public function getDuration()
    {
        return $this->duration;
    }

    protected $message = null;

    public function getMessage()
    {
        return $this->message;
    }

    protected $exception = null;

    public function getException()
    {
        return $this->exception;
    }

    protected function setException(Exception $e)
    {
        $parts = array();
        $code = $e->getCode();
        if (!empty($code)) {
            $parts[] = '#' . $code;
        }

        $parts[] = $e->getMessage();
        $parts[] = str_replace(APP_ROOT, '', $e->getFile()) . ':' . $e->getLine();

        if ($e instanceof Application_Exception) {
            $parts[] = $e->getDeveloperInfo();
        }

        $this->exception = implode(' | ', $parts);
    }

    protected function setError($message = null)
    {
        $this->setState(self::STATE_ERROR, $message);
    }

    protected function setOK($message = null)
    {
        $this->setState(self::STATE_OK, $message);
    }

    protected function setWarning($message = null)
    {
        $this->setState(self::STATE_WARNING, $message);
    }

    private function setState($level, $message = null)
    {
        $this->state = $level;

        if (!empty($message)) {
            $this->message = $message;
        }
    }

    public function hasDuration()
    {
        return isset($this->duration);
    }

    public function hasMessage()
    {
        return isset($this->message);
    }

    public function hasException()
    {
        return isset($this->exception);
    }

    private $durationStart;

    /**
     * Starts capturing the duration of the component. Use the
     * sister method, {@link durationStop()} to end capturing the
     * duration and save the duration.
     */
    protected function durationStart()
    {
        $this->durationStart = microtime(true);
    }

    /**
     * Stops capturing the duration of the component: calculates
     * the duration it took since the {@link durationStart()} call
     * and stores a value in microseconds. Returns the stored value.
     *
     * @return integer
     */
    protected function durationStop()
    {
        if (!isset($this->durationStart)) {
            return 0;
        }

        $end = microtime(true);
        $microseconds = intval(ceil(($end - $this->durationStart) * 1000));
        $this->duration = $microseconds;

        return $microseconds;
    }

    public function isError()
    {
        if ($this->state == self::STATE_ERROR) {
            return true;
        }

        return false;
    }

    public function isWarning()
    {
        if ($this->state == self::STATE_WARNING) {
            return true;
        }

        return false;
    }

    public function isOK()
    {
        if ($this->state == self::STATE_OK) {
            return true;
        }

        return false;
    }
}