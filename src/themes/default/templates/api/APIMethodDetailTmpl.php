<?php

declare(strict_types=1);

namespace Application\Themes\DefaultTemplate\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\ReservedParamInterface;
use Application\MarkdownRenderer;
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

        $related = $this->method->getRelatedMethods();
        if(!empty($related)) {
            $relatedLinks = array();
            foreach($related as $method) {
                $relatedLinks[] = sb()->link($method->getMethodName(), $method->getDocumentationURL());
            }

            $props->add(t('Related methods'), implode(', ', $relatedLinks));
        }

        echo $props;

        $this->generateParamList();
        $this->generateRulesList();
        $this->generateExample();

        echo '<br>';

        echo $this->renderCleanFrame(OutputBuffering::get());
    }

    private function generateExample() : void
    {
        $example = $this->method->renderExample();

        if(empty($example)) {
            return;
        }

        $this->ui->createSection()
            ->setTitle(t('Example response'))
            ->collapse()
            ->setIcon(Maileditor::icon()->setType('code', 'fas'))
            ->setContent($example)
            ->display();
    }

    private function generateParamList() : void
    {
        $grid = $this->ui->createDataGrid('api-method-params');
        $grid->enableCompactMode();
        $grid->disableFooter();
        $grid->addColumn('name', t('Parameter'))->setNowrap();
        $grid->addColumn('required', t('Required'));
        $grid->addColumn('type', t('Type'))->setNowrap();
        $grid->addColumn('description', t('Description'));

        $markdown = MarkdownRenderer::create();

        $entries = array();
        foreach($this->getParamsSorted() as $param) {
            $name = sb()->mono($param->getName());
            if($param instanceof ReservedParamInterface) {
                $name->add(UI::label(t('System'))->makeInfo()->setTooltip('This is a global system parameter, it is not specific to this method.'));
            }

            $entries[] = array(
                'name' => $name,
                'type' => $param->getTypeLabel(),
                'description' => $markdown->render($param->getDescription()),
                'required' => UI::prettyBool($param->isRequired())->makeYesNo()->makeDangerous()
            );
        }

        $this->ui->createSection()
                ->setTitle(t('Supported parameters'))
                ->setIcon(UI::icon()->variables())
                ->expand()
                ->setContent($grid->render($entries))
                ->display();
    }

    /**
     * @return APIParameterInterface[]
     */
    private function getParamsSorted() : array
    {
        $params = $this->method->manageParams()->getParams();

        usort($params, static function($a, $b) : int {
            $aIsReserved = $a instanceof ReservedParamInterface;
            $bIsReserved = $b instanceof ReservedParamInterface;
            if ($aIsReserved === $bIsReserved) {
                return 0; // preserve order
            }
            return $aIsReserved ? -1 : 1;
        });

        return $params;
    }

    private function generateRulesList() : void
    {
        foreach($this->method->manageParams()->getRules() as $rule)
        {
            $this->ui->createSection()
                ->setTitle($rule->getLabel())
                ->setIcon(Maileditor::icon()->setType('clipboard-check', 'fas'))
                ->collapse()
                ->setAbstract($rule->getDescription())
                ->setContent(sb()
                    ->add($rule->renderDocumentation($this->ui))
                        ->hr()
                        ->italic(sb()
                            ->t('This is a "%1$s" rule:', $rule->getTypeLabel())
                            ->add($rule->getTypeDescription())
                        )
                )
                ->display();
        }
    }
}
