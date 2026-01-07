<?php

declare(strict_types=1);

namespace Utilities;
use Application;

class CallableContainer
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * @var mixed[]
     */
    private $arguments = array();

    /**
     * @param callable $callable
     * @param mixed[] $arguments
     */
    public function __construct($callable, array $arguments = array())
    {
        $this->callable = $callable;
        $this->arguments = $arguments;

        Application::requireCallableValid($this->callable);
    }

    /**
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * @return mixed[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Calls the callback function, with the specified arguments.
     * If any arguments were added to the callback, they are
     * appended to these arguments.
     *
     * @param mixed[] $arguments
     * @return mixed
     */
    public function call(array $arguments = array())
    {
        $arguments = array_merge($arguments, $this->arguments);

        return call_user_func_array($this->callable, $arguments);
    }
}
