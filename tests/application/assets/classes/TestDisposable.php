<?php

class TestDisposable implements Application_Interfaces_Disposable
{
    use Application_Traits_Disposable;
    use Application_Traits_Eventable;
    use Application_Traits_Loggable;

    public const EVENT_EVENT_TRIGGERED = 'EventTriggered';

    private static int $counter = 0;
    private int $id;
    private bool $cleaned = false;

    /**
     * @var TestDisposable[]
     */
    private array $children = array();

    public function __construct()
    {
        self::$counter++;

        $this->id = self::$counter;
    }

    public function addChildren(int $amount) : void
    {
        for($i=0; $i < $amount; $i++)
        {
            $this->children[] = new TestDisposable();
        }
    }

    public function getIdentification() : string
    {
        return 'Test disposable #'.$this->id;
    }

    public function getChildDisposables() : array
    {
        return $this->children;
    }

    protected function _dispose() : void
    {
        $this->cleaned = true;
    }

    public function isCleaned() : bool
    {
        return $this->cleaned;
    }

    public function getLogIdentifier() : string
    {
        return sprintf('TestDisposable [%s]', self::$counter);
    }

    public function onEventTriggered(callable $callback) : void
    {
        $this->addEventListener(self::EVENT_EVENT_TRIGGERED, $callback);
    }

    public function triggerTheEvent() : void
    {
        $this->triggerEvent(self::EVENT_EVENT_TRIGGERED, array());
    }

    public function notAllowedAfterDisposing() : void
    {
        $this->requireNotDisposed('Not allowed');
    }
}
