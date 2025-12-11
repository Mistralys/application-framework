<?php

declare(strict_types=1);

namespace Application\Themes\DefaultTemplate\devel\appinterface;

use Application\MarkdownRenderer;
use UI\Admin\Screens\AppInterfaceDevelMode;
use AppUtils\Highlighter;
use Mistralys\Examples\UserInterface\ExampleFile;
use Mistralys\Examples\UserInterface\ExamplesCategory;
use UI;
use UI_Page_Template_Custom;

class ExampleTemplate extends UI_Page_Template_Custom
{
    protected function generateOutput(): void
    {
        $this->ui->addStylesheet('ui-appinterface.css');

        ?>
        <h2><?php echo $this->category->getTitle() ?> - <?php echo $this->example->getTitle() ?></h2>
        <?php
        $this->displayNavigation();

        $description = $this->example->getDescription();
        if (!empty($description)) {
            ?>
            <div class="example-description">
                <?php echo MarkdownRenderer::create()->render($description); ?>
            </div>
            <?php
        }

        if($this->example->hasOutput())
        {
            ?>
            <h4><?php pt('Example output'); ?></h4>
            <hr>
            <div class="example-output"><?php echo $this->example->renderOutput(); ?></div>
            <hr>
            <?php
        }
        ?>
        <h4><?php pt('Source code'); ?></h4>
        <?php
        echo Highlighter::php($this->example->getSourceCode());
        ?>
        <hr>
        <?php
        $this->displayNavigation();
    }

    protected function displayNavigation() : void
    {
        $examples = $this->category->getAll();
        $total = count($examples);
        $activeID = $this->example->getID();

        for($i=0; $i < $total; $i++)
        {
            $example = $examples[$i];
            if($example->getID() !== $activeID) {
                continue;
            }

            $prevE = $examples[($i-1)] ?? null;
            $prevB = UI::button(t('Previous'))
                ->setIcon(UI::icon()->previous());

            if($prevE !== null) {
                $prevB->link($prevE->getAdminViewURL());
                $prevB->setTooltip($prevE->getTitle());
            } else {
                $prevB->disable();
            }

            $nextE = $examples[($i+1)] ?? null;
            $nextB = UI::button(sb()->t('Next')->icon(UI::icon()->next()));

            if($nextE !== null) {
                $nextB->link($nextE->getAdminViewURL());
                $nextB->setTooltip($nextE->getTitle());
            } else {
                $nextB->disable();
            }

            echo $this->ui->createButtonGroup()
                ->addClass('example-navigation')
                ->addButton($prevB)
                ->addButton($nextB)
                ->render();
        }
    }

    protected ExampleFile $example;
    protected ExamplesCategory $category;

    protected function preRender(): void
    {
        $this->example = $this->getObjectVar(AppInterfaceDevelMode::TEMPLATE_VAR_ACTIVE_ID, ExampleFile::class);
        $this->category = $this->example->getCategory();
    }
}
