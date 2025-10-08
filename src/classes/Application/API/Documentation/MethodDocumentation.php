<?php

declare(strict_types=1);

namespace Application\API\Documentation;

use Application\API\APIMethodInterface;
use Application\Themes\DefaultTemplate\API\APIMethodDetailTmpl;
use UI_Page_Template;

class MethodDocumentation extends BaseAPIDocumentation
{
    private APIMethodInterface $method;

    public function __construct(APIMethodInterface $method)
    {
        $this->method = $method;

        parent::__construct();
    }

    protected function init(): void
    {
    }

    protected function getContentTemplate(): UI_Page_Template
    {
        return $this->page->createTemplate(APIMethodDetailTmpl::class)
            ->setVar(APIMethodDetailTmpl::PARAM_METHOD, $this->method);
    }
}
