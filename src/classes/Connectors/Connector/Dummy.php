<?php


class Connectors_Connector_Dummy extends Connectors_Connector
{
    protected function checkRequirements() : void
    {

    }

    public function getURL() : string
    {
        return 'http://google.com';
    }

    public function executeFailRequest()
    {
        $method = $this->createMethod('GetData');

        if($method instanceof Connectors_Connector_Dummy_Method_GetData)
        {
            $method->getData();
        }
    }
}
