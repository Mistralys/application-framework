<?php

declare(strict_types=1);

namespace Application\API\Traits;

use AppUtils\ArrayDataCollection;

trait JSONResponseWithExampleTrait
{
    private bool $exampleMode = false;

    public function isExampleResponse() : bool
    {
        return $this->exampleMode;
    }

    public function getExampleJSONResponse(): array
    {
        $this->exampleMode = true;

        $response = ArrayDataCollection::create();

        $this->collectResponseData($response, $this->getCurrentVersion());

        $this->exampleMode = false;

        return $response->getData();
    }
}
