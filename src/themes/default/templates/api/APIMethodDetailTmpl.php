<?php

declare(strict_types=1);

namespace Application\Themes\DefaultTemplate\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\MarkdownRenderer;
use AppUtils\OutputBuffering;
use UI_Page_Template_Custom;

class APIMethodDetailTmpl extends UI_Page_Template_Custom
{
    public const string PARAM_METHOD = 'method';

    private APIMethodInterface $method;

    protected function preRender(): void
    {
        $this->method = $this->getObjectVar(self::PARAM_METHOD, APIMethodInterface::class);
    }

    protected function generateOutput(): void
    {
        OutputBuffering::start();

        ?>
        <p>
            <a href="<?php echo APIManager::getInstance()->adminURL()->documentationOverview(); ?>">
                &laquo; <?php pt('Back to overview'); ?>
            </a>
        </p>
        <h1><?php pt('API'); echo ' - '.$this->method->getMethodName(); ?> </h1>
        <p class="abstract">
        <?php
            echo MarkdownRenderer::create()->render($this->method->getDescription());
        ?>
        </p>
        <?php

        $props = $this->ui->createPropertiesGrid();
        $props->add(t('Request mime'), $this->method->getRequestMime());
        $props->add(t('Response mime'), $this->method->getResponseMime());
        $props->add(t('Version'), $this->method->getCurrentVersion());
        $props->add(t('Versions'), implode(', ', $this->method->getVersions()));

        echo $props;

        $this->page->setTitle($this->method->getMethodName().' - '.t('%1$s API documentation', $this->driver->getAppNameShort()));

        echo $this->renderCleanFrame(OutputBuffering::get());
    }

}
