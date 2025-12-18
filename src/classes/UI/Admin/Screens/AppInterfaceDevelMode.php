<?php
/**
 * @package Application
 * @subpackage Admin
 */

declare(strict_types=1);

namespace UI\Admin\Screens;

use Application\Admin\Area\BaseMode;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\Development\Admin\DevScreenRights;
use Application\Themes\DefaultTemplate\devel\appinterface\ExampleOverviewTemplate;
use Application\Themes\DefaultTemplate\devel\appinterface\ExampleTemplate;
use Mistralys\Examples\InterfaceExamples;
use Mistralys\Examples\UserInterface\ExampleFile;
use Mistralys\Examples\UserInterface\ExamplesCategory;
use UI_Themes_Theme_ContentRenderer;

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
class AppInterfaceDevelMode extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'appinterface';

    public const string TEMPLATE_VAR_EXAMPLES = 'examples';
    public const string TEMPLATE_VAR_ACTIVE_ID = 'active';
    public const string REQUEST_PARAM_EXAMPLE_ID = 'example';
    public const string TEMPLATE_VAR_CATEGORIES = 'categories';

    /**
     * @var array<string,ExampleFile>
     */
    private array $examples = array();

    /**
     * @var ExampleFile|NULL
     */
    private ?ExampleFile $activeExample = null;

    /**
     * @var ExamplesCategory[]
     */
    private array $categories;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return DevScreenRights::SCREEN_APP_INTERFACE;
    }

    public function getDefaultSubscreenClass(): null
    {
        return null;
    }

    public function getTitle(): string
    {
        return t('Application interface reference');
    }

    public function getNavigationTitle(): string
    {
        return t('Interface refs');
    }

    public function getDevCategory(): string
    {
        return t('Documentation');
    }

    protected function _handleActions(): bool
    {
        $this->categories = new InterfaceExamples()->getAll();

        foreach ($this->categories as $category) {
            $examples = $category->getAll();
            foreach ($examples as $example) {
                $this->examples[$example->getScreenID()] = $example;
            }
        }

        $active = (string)$this->request->getParam(self::REQUEST_PARAM_EXAMPLE_ID);
        if (!empty($active) && isset($this->examples[$active])) {
            $this->activeExample = $this->examples[$active];
        }

        return true;
    }

    protected function _handleSidebar(): void
    {
        $activeCategoryID = null;
        $activeExampleID = null;
        if (isset($this->activeExample)) {
            $activeCategoryID = $this->activeExample->getCategory()->getID();
            $activeExampleID = $this->activeExample->getID();
        }

        foreach ($this->categories as $category) {
            $section = $this->sidebar->addSection()
                ->setGroup('app-interface')
                ->setTitle($category->getTitle())
                ->setCollapsed($category->getID() !== $activeCategoryID);

            $section->appendContent('<ul class="unstyled sidebar-examples-list">');

            foreach ($category->getAll() as $example) {
                $activeClass = '';
                if ($example->getID() === $activeExampleID) {
                    $activeClass = ' class="active"';
                }

                $section->appendContent(
                    '<li' . $activeClass . '>' .
                    '<a href="' . $example->getAdminViewURL() . '">' .
                    $example->getTitle() .
                    '</a>' .
                    '</li>'
                );
            }

            $section->appendContent('</ul>');
        }
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())->makeLinkedFromMode($this);
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->setTitle($this->getTitle());
    }

    protected function _renderContent(): UI_Themes_Theme_ContentRenderer
    {
        if (isset($this->activeExample)) {
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
