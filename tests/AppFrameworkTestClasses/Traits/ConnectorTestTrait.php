<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Traits;

use Application_AjaxMethod;
use AppUtils\ConvertHelper\JSONConverter;
use Connectors_Response;

trait ConnectorTestTrait
{
    public function assertResponseIsError(Connectors_Response $response): void
    {
        $this->assertTrue($response->isError());
        $this->assertResponseHasState($response, Application_AjaxMethod::STATE_ERROR);
    }

    public function assertResponseIsSuccess(Connectors_Response $response): void
    {
        $this->assertFalse($response->isError());
        $this->assertResponseHasState($response, Application_AjaxMethod::STATE_SUCCESS);
    }

    public function assertResponseHasState(Connectors_Response $response, string $state): void
    {
        $rawData = JSONConverter::json2array($response->getRawJSON());

        $this->assertArrayHasKey(Application_AjaxMethod::PAYLOAD_STATE, $rawData, print_r($rawData, true));
        $this->assertSame($state, $rawData[Application_AjaxMethod::PAYLOAD_STATE]);
    }
}
