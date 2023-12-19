<?php

declare(strict_types=1);

namespace Application\Themes\DefaultTemplate\devel\appinterface;

use Application_Admin_Area_Devel_Appinterface;
use Mistralys\Examples\UserInterface\ExampleFile;
use Mistralys\Examples\UserInterface\ExamplesCategory;
use UI_Page_Template_Custom;

class ExampleOverviewTemplate extends UI_Page_Template_Custom
{
    protected function generateOutput(): void
    {
        $this->ui->addStylesheet('ui-appinterface.css');

        ?>
        <p>
            <?php pt('The following is a collection of examples of user interface elements, intended as both a visual reference as well as for the related source code.'); ?>
        </p>
        <?php

        foreach($this->categories as $category)
        {
            $this->displayCategory($category);
        }
    }

    protected function displayCategory(ExamplesCategory $category) : void
    {
        $summary = $category->getSummary();

        ?>
        <h2><?php echo $category->getTitle(); ?></h2>
        <?php
        if(!empty($summary)) {
            ?>
            <p class="abstract"><?php echo $summary; ?></p>
            <?php
        }

        ?>
        <ul class="unstyled">
            <?php
            $examples = $category->getAll();
            foreach($examples as $example)
            {
                $this->displayExample($example);
            }
            ?>
        </ul>
        <?php
    }

    protected function displayExample(ExampleFile $file) : void
    {
        ?>
        <li>
            <a href="<?php echo $file->getAdminViewURL() ?>">
                <?php echo $file->getTitle() ?>
            </a>
        </li>
        <?php
    }

    /**
     * @var ExamplesCategory[]
     */
    protected array $categories;

    protected function preRender(): void
    {
        $this->categories = $this->getArrayVar(Application_Admin_Area_Devel_Appinterface::TEMPLATE_VAR_CATEGORIES);
    }
}
