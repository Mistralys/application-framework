<?php

declare(strict_types=1);

namespace TestDriver\Area\TestingScreen;

use Application\Admin\Screens\Events\BeforeContentRenderedEvent;
use Application_Admin_Area_Mode;
use TestDriver\Admin\TestingScreenInterface;
use TestDriver\Admin\TestingScreenTrait;
use UI_Themes_Theme_ContentRenderer;

class ReplaceContentScreen
    extends Application_Admin_Area_Mode
    implements TestingScreenInterface
{
    use TestingScreenTrait;

    public const URL_NAME = 'replace-content';

    public static function getTestLabel() : string
    {
        return t('Replace screen content via the before render event');
    }

    protected function _handleBeforeActions(): void
    {
        $this->onBeforeContentRendered(function (BeforeContentRenderedEvent $even) : void {
            $even->replaceScreenContentWith(
                (string)$even
                    ->getScreen()
                    ->getRenderer()
                    ->appendContent(sb()
                        ->para(sb()
                            ->success(sb()->bold(t('Test successful')))
                        )
                        ->para(
                            t(
                                'This text replaces the original content of the screen using the %1$s.event.',
                                BeforeContentRenderedEvent::EVENT_NAME
                            )
                        )
                    )
            );
        });
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->makeWithoutSidebar()
            ->appendContent(sb()
                ->para(sb()->danger(sb()->bold(t('Test failed'))))
                ->para(t('If this text is shown, the replacement text has failed.'))
            );
    }
}
