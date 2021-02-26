<?php

declare(strict_types=1);

abstract class Application_OAuth_Strategy
{
    abstract public function getLabel() : string;

    /**
     * Retrieves the configuration required by the strategy.
     * @return array<string,mixed>
     */
    abstract public function getConfig() : array;

    public function getName() : string
    {
        return getClassTypeName($this);
    }

    public function getLoginURL() : string
    {
        $request = Application_Driver::getInstance()->getRequest();

        return $request->buildURL(
            array(
                'strategy' => $this->getName()
            ),
            'login.php'
        );
    }
}
