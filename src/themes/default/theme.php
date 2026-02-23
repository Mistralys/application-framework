<?php 

declare(strict_types=1);

class Theme_default extends UI_Themes_Theme
{
    protected function _injectDependencies() : void
    {
        $this->ui->addJquery();
        $this->ui->addJqueryUI();
        $this->ui->addFontAwesome();
        $this->ui->addBootstrap();
        $this->ui->addSelect2();
    }
}
