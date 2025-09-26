<?php

declare(strict_types=1);

namespace Application\Themes\DefaultTemplate\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Parameters\ReservedParamInterface;
use Application\MarkdownRenderer;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\OutputBuffering;
use Maileditor;
use UI;
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
        $this->ui->addStylesheet('api/api-methods.css');

        $this->page->setTitle($this->method->getMethodName().' - '.t('%1$s API documentation', $this->driver->getAppNameShort()));

        OutputBuffering::start();

        ?>
        <p>
            <a href="<?php echo APIManager::getInstance()->adminURL()->documentationOverview(); ?>">
                &laquo; <?php pt('Back to overview'); ?>
            </a>
        </p>
        <h1><?php pt('API'); echo ' - '.$this->method->getMethodName(); ?> </h1>
        <div class="method-abstract">
        <?php
            echo MarkdownRenderer::create()->render($this->method->getDescription());
        ?>
        </div>
        <?php

        $props = $this->ui->createPropertiesGrid();
        $props->add(t('Request mime'), $this->method->getRequestMime());
        $props->add(t('Response mime'), $this->method->getResponseMime());
        $props->add(t('Version'), $this->method->getCurrentVersion());
        $props->add(t('Versions'), implode(', ', $this->method->getVersions()));

        echo $props;

        $this->generateParamList();
        $this->generateRulesList();
        $this->generateExample();

        echo $this->renderCleanFrame(OutputBuffering::get());
    }

    private function generateExample() : void
    {
        $this->ui->createSection()
            ->setTitle(t('Example response'))
            ->setIcon(Maileditor::icon()->setType('code', 'fas'))
            ->setContent('<pre>'.JSONConverter::var2json(array('foo' => 'bar'), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).'</pre>')
            ->display();
    }

    private function generateParamList() : void
    {
        $grid = $this->ui->createDataGrid('api-method-params');
        $grid->enableCompactMode();
        $grid->disableFooter();
        $grid->addColumn('name', t('Parameter'))->setNowrap();
        $grid->addColumn('type', t('Type'));
        $grid->addColumn('required', t('Required'));
        $grid->addColumn('description', t('Description'));

        $params = $this->method->manageParams()->getParams();

        usort($params, static function($a, $b) : int {
            $aIsReserved = $a instanceof ReservedParamInterface;
            $bIsReserved = $b instanceof ReservedParamInterface;
            if ($aIsReserved === $bIsReserved) {
                return 0; // preserve order
            }
            return $aIsReserved ? -1 : 1;
        });

        $entries = array();
        foreach($params as $param) {
            $name = sb()->mono($param->getName());
            if($param instanceof ReservedParamInterface) {
                $name->add(UI::label(t('System'))->makeInfo()->setTooltip('This is a global system parameter, it is not specific to this method.'));
            }

            $entries[] = array(
                'name' => $name,
                'type' => $param->getTypeLabel(),
                'description' => $param->getDescription(),
                'required' => UI::prettyBool($param->isRequired())->makeYesNo()->makeDangerous()
            );
        }

        $this->ui->createSection()
                ->setTitle(t('Supported parameters'))
                ->setIcon(UI::icon()->variables())
                ->setContent($grid->render($entries))
                ->display();
    }

    private function generateRulesList() : void
    {
        foreach($this->method->manageParams()->getRules() as $rule)
        {
            $this->ui->createSection()
                ->setTitle($rule->getLabel())
                ->setIcon(Maileditor::icon()->setType('clipboard-check', 'fas'))
                ->setAbstract($rule->getDescription())
                ->setContent(sb()
                    ->add($rule->renderDocumentation($this->ui))
                        ->hr()
                        ->t('This is a "%1$s" rule:', $rule->getTypeLabel())->add($rule->getTypeDescription())
                )
                ->display();
        }
    }
}
