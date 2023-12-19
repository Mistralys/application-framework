<?php
/**
 * File containing the {@see Application_Admin_Area_Devel_Appinterface} class.
 *
 * @package Application
 * @subpackage Admin
 * @see Application_Admin_Area_Devel_Appinterface
 */

declare(strict_types=1);

use Application\Themes\DefaultTemplate\devel\appinterface\ExampleOverviewTemplate;
use Application\Themes\DefaultTemplate\devel\appinterface\ExampleTemplate;
use Mistralys\Examples\InterfaceExamples;
use Mistralys\Examples\UserInterface\ExampleFile;
use Mistralys\Examples\UserInterface\ExamplesCategory;

/**
 * Abstract base class used to display a reference of application UI
 * elements that can be used when building administration screens.
 * It creates a live menu to choose which examples to show.
 *
 * The examples themselves are stored as templates, under
 * `templates/appinterface`, with folders corresponding to the
 * example's category ID.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Admin_Area_Devel_Appinterface extends Application_Admin_Area_Mode
{
    public const URL_NAME = 'appinterface';

    public const TEMPLATE_VAR_EXAMPLES = 'examples';
    public const TEMPLATE_VAR_ACTIVE_ID = 'active';
    public const REQUEST_PARAM_EXAMPLE_ID = 'example';
    public const TEMPLATE_VAR_CATEGORIES = 'categories';
    /**
    * @var array<string,ExampleFile>
    */
    private array $examples=array();

   /**
    * @var string[]
    */
    private array $exampleIDs = array();

   /**
    * @var ExampleFile|NULL
    */
    private ?ExampleFile $activeExample = null;

    /**
     * @var ExamplesCategory[]
     */
    private array $categories;

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getTitle() : string
    {
        return t('Application interface reference');
    }

    public function getNavigationTitle() : string
    {
        return t('Interface refs');
    }

    public function getDefaultSubmode() : string
    {
        return '';
    }

    public function isUserAllowed() : bool
    {
        return $this->user->isDeveloper();
    }

    /**
     * @return ExamplesCategory[]
     */
    protected function getExamplesList() : array
    {
        return (new InterfaceExamples())->getAll();

        return array(
            'content-sections' => array(
                'label' => t('Content sections'),
                'examples' => array(
                    'section' => t('Basic usage'),
                    'section-adding-html' => t('Adding HTML contents'),
                    'section-info' => t('Informational message section'),
                    'section-compact' => t('Compact mode'),
                    'section-itemslist' => t('Adding an items list'),
                    'section-form' => t('Adding a form'),
                    'section-quickselector' => t('Adding a quick selector'),
                    'section-subsection' => t('Adding a subsection'),
                    'section-buttons' => t('Adding context buttons'),
                )
            ),
            'data-grids-db' => array(
                'label' => t('Data Grids (DB)'),
                'examples' => array(
                    '_grid-setup' => t('Grid example setup'),
                    'without-paging' => t('Without paging'),
                    'with-paging' => t('With paging')
                )
            ),
            'data-grids-non-db' => array(
                'label' => t('Data Grids (No DB)'),
                'examples' => array(
                    '_grid-setup' => t('Grid example setup'),
                    'without-paging' => t('Without paging'),
                    'manual-paging' => t('With manual paging'),
                )
            ),
            'ui-elements' => array(
                'label' => t('UI elements'),
                'examples' => array(
                    'buttons-styles' => t('Button styles'),
                    'pretty-bool' => t('Pretty booleans'),
                    'copy-text-codeblock' => t('Copy text code block'),
                    'country-flags' => t('Country flags'),
                    'badges' => t('Badges and labels'),
                    'text-colors' => t('Text colors'),
                    'system-hints' => t('System hints'),
                    'alerts' => t('Alerts'),
                    'navigations' => t('Navigations'),
                    'property-grids' => t('Property grids'),
                )
            ),
            'forms' => array(
                'label' => t('Forms'),
                'examples' => array(
                    'sections' => t('Automatic sections'),
                    'tabs' => t('With tabs'),
                    'custom-elements' => t('Custom app input elements'),
                    'footer-buttons' => t('Footer with buttons')
                )
            ),
            'form-elements' => array(
                'label' => t('Form elements'),
                'examples' => array(
                    'visual-select' => t('Visual select'),
                    'visual-select-with-image-sets' => sb()->t('Visual select')->add('-')->t('With image sets'),
                    'visual-select-multiple' => sb()->t('Visual select')->add('-')->t('Multiple form elements'),
                    'markup-editor' => t('Markup editor')
                )
            ),
            'errors' => array(
                'label' => t('Error handling'),
                'examples' => array(
                    'exception-html' => t('The exception page, in %1$s format', 'HTML'),
                    'exception-text' => t('The exception page, in %1$s format', 'TEXT'),
                )
            )
        );
    }

    protected function _handleActions() : bool
    {
        $this->categories = (new InterfaceExamples())->getAll();

        foreach($this->categories as $category)
        {
            $examples = $category->getAll();
            foreach($examples as $example)
            {
                $this->examples[$example->getScreenID()] = $example;
            }
        }

        $active = (string)$this->request->getParam(self::REQUEST_PARAM_EXAMPLE_ID);
        if(!empty($active) && isset($this->examples[$active]))
        {
            $this->activeExample = $this->examples[$active];
        }

        return true;
    }

    protected function _handleSidebar() : void
    {
        $activeCategoryID = null;
        $activeExampleID = null;
        if(isset($this->activeExample)) {
            $activeCategoryID = $this->activeExample->getCategory()->getID();
            $activeExampleID = $this->activeExample->getID();
        }

        foreach($this->categories as $category)
        {
            $section = $this->sidebar->addSection()
                ->setGroup('app-interface')
                ->setTitle($category->getTitle())
                ->setCollapsed($category->getID() !== $activeCategoryID);

            $section->appendContent('<ul class="unstyled sidebar-examples-list">');

            foreach($category->getAll() as $example)
            {
                $activeClass = '';
                if($example->getID() === $activeExampleID) {
                    $activeClass = ' class="active"';
                }

                $section->appendContent(
                    '<li'.$activeClass.'>'.
                        '<a href="'.$example->getAdminViewURL().'">'.
                            $example->getTitle().
                        '</a>'.
                    '</li>'
                );
            }

            $section->appendContent('</ul>');
        }
    }

    protected function _handleBreadcrumb() : void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())->makeLinkedFromMode($this);
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->setTitle($this->getTitle());
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        if(isset($this->activeExample)) {
            $this->renderer->appendTemplateClass(
                ExampleTemplate::class,
                array(
                    self::TEMPLATE_VAR_CATEGORIES => $this->categories,
                    self::TEMPLATE_VAR_EXAMPLES => $this->examples,
                    self::TEMPLATE_VAR_ACTIVE_ID => $this->activeExample
                )
            );
        } else {
            $this->renderer->appendTemplateClass(
                ExampleOverviewTemplate::class,
                array(
                    self::TEMPLATE_VAR_CATEGORIES => $this->categories
                )
            );
        }

        return $this->renderer
            ->makeWithSidebar();
    }
}
