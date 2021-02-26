<?php 

class Theme_default extends UI_Themes_Theme
{
    protected function _injectDependencies()
    {
        $this->ui->addJquery();
        $this->ui->addJqueryUI();
        $this->ui->addFontAwesome();
        $this->ui->addBootstrap();
    }
}