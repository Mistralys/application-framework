<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Traits;

use AppFrameworkTestClasses\ApplicationTestCaseInterface;
use Connectors_Response;

interface ConnectorTestInterface extends ApplicationTestCaseInterface
{
    public function assertResponseIsError(Connectors_Response $response) : void;
    public function assertResponseIsSuccess(Connectors_Response $response) : void;
    public function assertResponseHasState(Connectors_Response $response, string $state) : void;
}
