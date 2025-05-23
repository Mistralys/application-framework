<?php
/**
 * @package Application
 * @subpackage Sessions
 */

use AppUtils\NamedClosure;

/**
 * Session implementation using the native PHP session functions.
 *
 * @package Application
 * @subpackage Sessions
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Session_Native extends Application_Session_Base
{
    public const ERROR_CANNOT_START_SESSION = 101001;

    public const SESSION_HEADERS_ALREADY_SENT = 8;

    /**
     * @var array<string,string>
     */
    private static array $options = array();
    private bool $enabled = true;

    protected function init(): void
    {
        $this->enabled = !isCLI();
    }

    protected function _start(): void
    {
        $name = $this->getName();

        if(!$this->enabled) {
            $this->log('Start | IGNORE | Session handling is not enabled.');
            return;
        }

        $this->log('Start | Using session name [%s].', $name);

        // Temporarily set an error handler to catch session
        // initialization errors, so they can be converted
        // to an exception.
        set_error_handler(NamedClosure::fromClosure(
            Closure::fromCallable(array($this, 'callback_sessionStartError')),
            array($this, 'callback_sessionStartError')
        ));

        session_name($name);
        session_start(self::$options);

        restore_error_handler();

        $this->log('Start | Initial session payload:');
        $this->logData($_SESSION);
    }

    private function callback_sessionStartError(int $code, string $msg, string $file, int $line) : bool
    {
        $ex = new Application_Session_Exception(
            'Session handling initialization failed',
            'The session_start() function failed.',
            self::ERROR_CANNOT_START_SESSION
        );

        $ex->setErrorDetails($code, $msg, $file, $line);

        throw $ex;
    }

    public function isEnabled() : bool
    {
        return $this->enabled;
    }

    public function getID() : string
    {
        if(!$this->enabled) {
            return 'not-started';
        }

        return session_id();
    }

    /**
     * Sets a session option.
     *
     * > NOTE: Must be used before the session is started.
     * > Has no effect afterward.
     *
     * ## Example
     *
     * ```php
     * Application_Session_Native::setOption('cookie_lifetime', '60');
     * ```
     *
     * @param string $name
     * @param string $value
     * @return void
     *
     * @link https://www.php.net/manual/en/session.configuration.php
     */
    public static function setOption(string $name, string $value) : void
    {
        self::$options[$name] = $value;
    }

    /**
     * Retrieves the value of a session option.
     *
     * @param string $name The name, with or without `session.` prefix, as they are stored in `php.ini`.
     * @return string An empty string is returned if it does not exist.
     */
    public static function getOption(string $name) : string
    {
        $value = ini_get('session.'.str_replace('session.', '', $name));

        if($value !== false)
        {
            return $value;
        }

        return '';
    }

    protected function _destroy() : void
    {
        if($this->enabled) {
            session_destroy();
            return;
        }

        $_SESSION = array();
    }

    public function getValue(string $name, $default = null)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }

        return $default;
    }

    public function setValue(string $name, $value) : void
    {
        $_SESSION[$name] = $value;
    }

    public function valueExists(string $name) : bool
    {
        return isset($_SESSION[$name]);
    }

    public function unsetValue(string $name) : void
    {
        unset($_SESSION[$name]);
    }
}
