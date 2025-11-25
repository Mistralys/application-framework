<?php
/**
 * @package API
 * @subpackage UI
 */

declare(strict_types=1);

namespace Application\Themes\DefaultTemplate\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Connector\AppAPIConnector;
use Application\API\Connector\AppAPIMethod;
use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\Flavors\APIHeaderParameterInterface;
use Application\API\Parameters\Flavors\RequiredOnlyParamInterface;
use Application\API\Parameters\ReservedParamInterface;
use Application\API\Parameters\ValueLookup\SelectableValueParamInterface;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Utilities\KeyDescription;
use Application\MarkdownRenderer;
use Application_Bootstrap;
use AppUtils\ArrayDataCollection;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\Highlighter;
use AppUtils\OutputBuffering;
use Connectors;
use Connectors\Headers\HTTPHeadersBasket;
use Connectors_Exception;
use Connectors_Response;
use Connectors_ResponseCode;
use Throwable;
use UI;
use UI\CSSClasses;
use UI_Form;
use UI_Page_Template_Custom;

/**
 * Renders the API method detail documentation page.
 *
 * @package API
 * @subpackage UI
 */
class APIMethodDetailTmpl extends UI_Page_Template_Custom
{
    public const string PARAM_METHOD = 'method';

    private APIMethodInterface $method;
    private UI_Form $form;


    protected function preRender(): void
    {
        $this->method = $this->getObjectVar(self::PARAM_METHOD, APIMethodInterface::class);

        $this->createForm();
    }

    private function generateURL(ArrayDataCollection $values) : string
    {
        $params = $this->getParamsSorted();

        $urlParams['method'] = $this->method->getMethodName();
        foreach($params as $param)
        {
            if($param instanceof APIHeaderParameterInterface) {
                continue;
            }

            $value = $values->getString($param->getName());
            if(!empty($value)) {
                $urlParams[$param->getName()] = $value;
            }
        }

        return APP_URL.'/api/?'.http_build_query($urlParams, '', '&', PHP_QUERY_RFC3986);
    }

    private function handleFormActions(ArrayDataCollection $values) : void
    {
        $url = $this->generateURL($values);

        echo '<br>';

        $this->ui->createMessage()
            ->makeInfo()
            ->enableIcon()
            ->makeNotDismissable()
            ->setContent(sb()
                ->add('API URL generated for your selected parameters:')
                ->para(sb()->link($url, $url, true))
            )
            ->display();

        if(!$this->request->getBool('generate_url'))
        {
            try
            {
                $data = $this->fetchAPIResult($values);
                $json = JSONConverter::var2json($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

                if($data[JSONResponseInterface::RESPONSE_KEY_STATE] === JSONResponseInterface::RESPONSE_STATE_SUCCESS) {
                    $state = UI::label('OK')->makeSuccess();
                } else {
                    $state = UI::label('Error')->makeDangerous();
                }

                OutputBuffering::start();
                $length = strlen($json);
                ?>
                <style>
                    .response > PRE{ max-height: 400px; overflow: auto; }
                </style>
                <div class="response">
                    <?php echo Highlighter::json($json) ?>
                </div>
                <?php
                $output = OutputBuffering::get();
            }
            catch (Throwable $e)
            {
                $exception = Application_Bootstrap::convertException($e);
                $exception->log();

                $state = UI::label('Exception')->makeDangerous();

                $length = 0;
                $output = $this->ui->createMessage()
                    ->makeError()
                    ->enableIcon()
                    ->makeNotDismissable()
                    ->setContent(sb()
                        ->bold('An exception occurred while trying to perform the API request:')
                        ->sf('#%1$s', $exception->getCode())
                        ->quote($exception->getMessage())
                    );
            }

            $this->ui->createSection()
                ->setTitle(sb()->t('%1$s response', 'JSON')->add($state))
                ->setAbstract(sb()->t('Response size:')->add(ConvertHelper::bytes2readable($length)))
                ->setIcon(UI::icon()->setType('code', 'fas'))
                ->setContent($output)
                ->expand()
                ->display();
        }
    }

    private function fetchAPIResult(ArrayDataCollection $values) : array
    {
        return AppAPIConnector::create(APP_URL)
            ->fetchMethodData(
                $this->method->getMethodName(),
                $values->getData(),
                $this->getHeaders($values)
            )
            ->getData();
    }

    /**
     * Sets the headers for any header-based parameters of the method
     * for the "Try it out" request.
     *
     * @param ArrayDataCollection $values
     * @return HTTPHeadersBasket
     */
    private function getHeaders(ArrayDataCollection $values) : HTTPHeadersBasket
    {
        $headers = new HTTPHeadersBasket();

        foreach($this->method->manageParams()->getHeaderParams() as $param)
        {
            $headerValue = $values->getString($param->getName());
            if(!empty($headerValue)) {
                $param->injectHeaderForValue($headers, $headerValue);
            }
        }

        return $headers;
    }

    protected function generateOutput(): void
    {
        $this->ui->addStylesheet('api/api-methods.css');

        $this->page->setTitle($this->method->getMethodName().' - '.t('%1$s API documentation', $this->driver->getAppNameShort()));

        OutputBuffering::start();

        ?>
        <p class="pull-left">
            <a href="<?php echo APIManager::getInstance()->adminURL()->documentationOverview(); ?>">
                &laquo; <?php pt('Back to overview'); ?>
            </a>
        </p>
        <?php

        $this->getPage()->createTemplate(APIMethodsMetaNav::class)->display();

        ?>
        <h1><?php pt('API'); echo ' - '.$this->method->getMethodName(); ?> </h1>
        <?php

        if($this->form->isSubmitted() && $this->form->isValid()) {
            $this->handleFormActions(ArrayDataCollection::create($this->form->getValues()));
        } else {
            $this->generateDocs();
        }

        $this->generateRequestBuilder();

        echo '<br>';

        echo $this->renderCleanFrame(OutputBuffering::get());
    }

    private function generateDocs() : void
    {
        ?>
        <div class="method-abstract">
            <?php
            echo MarkdownRenderer::create()->render(APIManager::getInstance()->markdownifyMethodNames($this->method->getDescription()));
            ?>
        </div>
        <?php
        $this->generateProperties();
        $this->generateHeaderList();
        $this->generateParamList();
        $this->generateRulesList();
        $this->generateExample();
        $this->generateKeyDescriptions();
        $this->generateChangelog();
    }

    private function generateKeyDescriptions() : void
    {
        if(!$this->method instanceof JSONResponseInterface) {
            return;
        }

        $keys = $this->method->getReponseKeyDescriptions();

        if(empty($keys)) {
            return;
        }

        usort($keys, static function(KeyDescription $a, KeyDescription $b) : int {
            return strnatcasecmp($a->getPath(), $b->getPath());
        });

        $items = array();
        foreach($keys as $key) {
            $items[] = sb()->code($key->getPath()).' - '.$key->renderDescription();
        }

        $this->ui->createSection()
            ->setTitle('Response keys')
            ->setAbstract(sb()
                ->add('This documents some keys returned in the JSON response whose purpose or meaning may require clarification.')
                ->note()
                ->add('They are sorted alphabetically here.')
            )
            ->setIcon(UI::icon()->information())
            ->collapse()
            ->setContent(sb()->ul($items))
            ->display();
    }

    private function generateChangelog() : void
    {
        $changelog = $this->method->getChangelog();
        if(empty($changelog)) {
            return;
        }

        $output = '';
        foreach($changelog as $version => $changes) {
            $output .= sprintf("\n## v%s\n%s", $version, $changes);
        }

        $this->ui->createSection()
            ->setTitle(t('Changelog'))
            ->setIcon(UI::icon()->changelog())
            ->collapse()
            ->setContent(MarkdownRenderer::create()->render($output))
            ->display();
    }

    private function generateProperties() : void
    {
        $props = $this->ui->createPropertiesGrid();
        $props->add(t('API group'), $this->method->getGroup()->getLabel());
        $props->add(t('Request mime'), $this->method->getRequestMime());
        $props->add(t('Response mime'), $this->method->getResponseMime());
        $props->add(t('Version'), $this->method->getCurrentVersion());
        $props->add(t('Versions'), implode(', ', $this->method->getVersions()));
        $props->add(t('HTTP status codes'), implode('<br>', $this->resolveHTTPStatusCodes()));

        $related = $this->method->getRelatedMethods();
        if(!empty($related)) {
            $relatedLinks = array();
            foreach($related as $method) {
                $relatedLinks[] = sb()->link($method->getMethodName(), $method->getDocumentationURL());
            }

            $props->add(t('Related methods'), implode(', ', $relatedLinks));
        }

        echo $props;
    }

    private function resolveHTTPStatusCodes() : array
    {
        $codes = array(
            Connectors_ResponseCode::HTTP_OK => 'OK (successful request)',
            Connectors_ResponseCode::HTTP_BAD_REQUEST => 'Bad request (missing or invalid parameters)',
            Connectors_ResponseCode::HTTP_INTERNAL_SERVER_ERROR => 'Internal server error',
        );

        $result = array();
        foreach($codes as $code => $desc) {
            $result[] = sb()->code($code)->italic($desc);
        }

        return $result;
    }

    private function generateExample() : void
    {
        try {
            $example = $this->method->renderExample();
        }
        catch (Throwable $e) {
            $example = 'ERROR: '.$e->getMessage();
        }

        if(empty($example)) {
            return;
        }

        $this->ui->createSection()
            ->setTitle(t('Example response'))
            ->collapse()
            ->setIcon(UI::icon()->setType('code', 'fas'))
            ->setContent($example)
            ->display();
    }

    private function generateHeaderList() : void
    {
        $params = $this->getHeaderParams();
        if(empty($params)) {
            return;
        }

        $markdown = MarkdownRenderer::create();

        $grid = $this->ui->createDataGrid('api-method-headers');
        $grid->enableCompactMode();
        $grid->disableFooter();
        $grid->addColumn('header', t('Header'))->setNowrap();
        $grid->addColumn('description', t('Description'));
        $grid->addColumn('required', t('Required'));

        $entries = array();
        foreach($params as $param) {
            if(!$param instanceof APIHeaderParameterInterface) {
                continue;
            }

            $entries[] = array(
                'header' => sb()->mono($param->getHeaderExample()),
                'description' => $markdown->render($param->getDescription()),
                'required' => UI::prettyBool($param->isRequired())->makeYesNo()->makeDangerous()
            );
        }

        $this->ui->createSection()
                ->setTitle('Request headers')
                ->setAbstract('These are any HTTP headers that the client can or must include in the API request.')
                ->setIcon(UI::icon()->variables())
                ->collapse()
                ->setContent($grid->render($entries))
                ->display();
    }

    private function getHeaderParams() : array
    {
        $results = array();
        foreach($this->getParamsSorted() as $param) {
            if(!$param instanceof APIHeaderParameterInterface) {
                continue;
            }

            $results[] = $param;
        }

        return $results;
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
                ->collapse()
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
                ->setIcon(UI::icon()->setType('clipboard-check', 'fas'))
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

    private function generateRequestBuilder() : void
    {
        $this->ui->createSection()
            ->setTitle(t('Try it out'))
            ->setCollapsed(!$this->form->isSubmitted())
            ->setIcon(UI::icon()->setType('play', 'fas'))
            ->setAbstract(sb()
                ->add('Fill out the parameters you wish to include in the request.')
                ->add('Note:')
                ->add('By design, none of the parameters are marked as required.')
                ->add('This allows you to set it up freely for testing purposes.')
                ->add('Refer to the parameter documentation to verify their dependencies and requirements.')
            )
            ->setContent($this->form->render())
            ->display();
    }

    private function createForm() : void
    {
        $defaults = array();
        $params = $this->getParamsSorted();

        foreach($params as $param)
        {
            if($param instanceof ReservedParamInterface && !$param->isEditable()) {
                continue;
            }

            if($param instanceof SelectableValueParamInterface) {
                $defaults[$param->getName()] = $param->getDefaultSelectableValue()?->getValue() ?? '';
            }
        }

        $form = $this->ui->createForm($this->method->getMethodName().'-request-builder-form', $defaults);
        $form->addHiddenVar('method', $this->method->getMethodName());

        foreach($params as $param)
        {
            if($param instanceof ReservedParamInterface && !$param->isEditable()) {
                continue;
            }

            if($param instanceof SelectableValueParamInterface)
            {
                $field = $form->addSelect($param->getName(), $param->getLabel());

                $field->addOption('Please select...', '');

                foreach($param->getSelectableValues() as $value) {
                    $field->addOption($value->getLabel(), $value->getValue());
                }
            } else {
                $field = $form->addText($param->getName(), $param->getLabel());
            }

            $field->addClass(CSSClasses::INPUT_XXLARGE);
            $field->setComment($param->getDescription());

            if($param instanceof APIHeaderParameterInterface) {
                $field->setLabel($field->getLabel().' '.UI::label('Header')->makeInfo()->setTooltip('This parameter is sent as an HTTP header in the API request.'));
            }

            if($param instanceof RequiredOnlyParamInterface) {
                $form->makeRequired($field);
            }
        }

        $form->addPrimarySubmit('Send request')
                ->setIcon(UI::icon()->setType('play', 'fas'));

        $form->addButton('generate_url')
                ->setValue('yes')
                ->setLabel(t('Show API request URL'))
                ->setIcon(UI::icon()->link())
                ->setAttribute('onclick', 'alert("argh");');

        if($form->isSubmitted()) {
            $form->addButton('back')
                ->setLabel('Stop testing')
                ->setIcon(UI::icon()->back())
                ->setAttribute('style', 'float:right')
                ->link($this->method->getDocumentationURL());
        }

        $this->form = $form;
    }
}
