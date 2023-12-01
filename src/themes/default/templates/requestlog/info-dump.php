<?php
/**
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_requestlog_info_dump
 */

declare(strict_types=1);

use AppUtils\OutputBuffering;
use function AppUtils\parseVariable;

/**
 * Template for the request log's info dump screen.
 *
 * @package UserInterface
 * @subackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Bootstrap_Screen_RequestLog::renderInfoDump()
 */
class template_default_requestlog_info_dump extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        OutputBuffering::start();

        echo $this->renderTemplate('requestlog/header');

        ksort($_SERVER);

        $this->ui->createSection()
            ->setTitle(t('Server variables'))
            ->setContent('<pre>'.print_r($_SERVER, true).'</pre>')
            ->collapse()
            ->display();

        ksort($_SESSION);

        $this->ui->createSection()
            ->setTitle(t('Session variables'))
            ->setContent('<pre>'.print_r($_SESSION, true).'</pre>')
            ->collapse()
            ->display();

        $this->ui->createSection()
            ->setTitle(t('Configuration settings'))
            ->setContent($this->renderConfigSettings())
            ->collapse()
            ->display();

        ?>
        <?php

        echo $this->renderCleanFrame(OutputBuffering::get());
    }

    private function renderConfigSettings() : string
    {
        $grid = $this->ui->createDataGrid('config-dump');
        $grid->addColumn('name', t('Name'));
        $grid->addColumn('value', t('Value'));
        $grid->addColumn('required', t('Required'))->alignCenter();

        $settings = Application_Bootstrap::getKnownSettings();
        $entries = array();
        foreach ($settings as $name => $def) {
            $entries[] = array(
                'name' => $name,
                'value' => parseVariable(boot_constant($name))->enableType()->toString(),
                'required' => UI::prettyBool($def['required'])->makeWarning()
            );
        }

        return $grid->render($entries);
    }

    protected function preRender(): void
    {
    }
}
