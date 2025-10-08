<?php

declare(strict_types=1);

namespace Application\API\Documentation;

use Application\API\APIManager;
use Application\Themes\DefaultTemplate\API\APIMethodsOverviewTmpl;
use UI_Page_Template;

class APIDocumentation extends BaseAPIDocumentation
{
    protected function init(): void
    {

    }

    protected function getContentTemplate(): UI_Page_Template
    {
        return $this->getUI()
            ->createTemplate(APIMethodsOverviewTmpl::class)
            ->setVar(APIMethodsOverviewTmpl::PARAM_METHODS, APIManager::getInstance()->getMethodCollection()->getAll());
    }
}
