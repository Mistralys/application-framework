<?php

declare(strict_types=1);

class Application_Traits_Disposable_Event_Disposed extends Application_EventHandler_EventableEvent
{
    /**
     * @return Application_Interfaces_Disposable
     * @throws Application_Exception_UnexpectedInstanceType
     */
    public function getDisposable() : Application_Interfaces_Disposable
    {
        $disposable = $this->getArgument(0);

        if($disposable instanceof Application_Interfaces_Disposable)
        {
            return $disposable;
        }

        throw new Application_Exception_UnexpectedInstanceType(Application_Interfaces_Disposable::class, $disposable);
    }
}
