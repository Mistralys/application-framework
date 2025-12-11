<?php

declare(strict_types=1);

namespace TestDriver\Area\TestingScreen;

use Application\Development\Admin\AppDevAdminURLs;
use Application_Admin_Area_Mode;
use TestDriver\Admin\TestingScreenInterface;
use TestDriver\Admin\TestingScreenTrait;
use UI;
use UI_Themes_Theme_ContentRenderer;

class LogJavaScriptErrorScreen extends Application_Admin_Area_Mode
    implements TestingScreenInterface
{
    use TestingScreenTrait;

    public const URL_NAME = 'log-javascript-error';

    public static function getTestLabel(): string
    {
        return t('Trigger a JavaScript error to test the error logging');
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->makeWithoutSidebar()
            ->appendContent(sb()
                ->add(UI::button(t('Trigger a script error'))
                    ->setTooltip(t('Click to attempt to call an unknown function.'))
                    ->click('UnknownFunction()')
                )
                ->add(UI::button(t('Trigger an exception'))
                    ->setTooltip(t('Click to throw a regular exception.'))
                    ->click("const exceptionCause = new Error('I am the cause');throw new Error('Test exception', {cause:exceptionCause})")
                )
                ->add(UI::button(t('Trigger a framework exception'))
                    ->setTooltip(t('Click to throw a framework exception.'))
                    ->click("throw new ApplicationException('Test framework exception', 'Developer details', 654321);")
                )
            )
            ->appendContent('<hr>')
            ->appendContent(UI::button(t('Open the error log'))
                ->makePrimary()
                ->setIcon(UI::icon()->next())
                ->link(AppDevAdminURLs::getInstance()->errorLog(), '_blank')
            );
    }
}
