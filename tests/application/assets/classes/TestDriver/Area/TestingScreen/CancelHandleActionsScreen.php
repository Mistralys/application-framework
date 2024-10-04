<?php

declare(strict_types=1);

namespace TestDriver\Area\TestingScreen;

use Application\Admin\Screens\Events\BeforeActionsHandledEvent;
use Application_Admin_Area_Mode;
use TestDriver\Admin\TestingScreenInterface;
use TestDriver\Admin\TestingScreenTrait;
use UI_Themes_Theme_ContentRenderer;

class CancelHandleActionsScreen
    extends Application_Admin_Area_Mode
    implements TestingScreenInterface
{
    use TestingScreenTrait;

    public const URL_NAME = 'cancel-handle-actions';
    private bool $processed = false;

    public static function getTestLabel() : string
    {
        return t('Cancel a screen\'s handling of actions');
    }

    protected function _handleBeforeActions(): void
    {
        $this->onBeforeActionsHandled(function (BeforeActionsHandledEvent $event) : void {
            $event->cancel('Testing');
        });
    }

    protected function _handleActions(): bool
    {
        $this->processed = true;
        return true;
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        if($this->processed) {
            return $this->renderer
                ->appendContent(sb()
                    ->para(sb()->danger(sb()->bold(t('Test failed'))))
                    ->para(t('If this text is shown, the cancellation of the action handling has failed.'))
                );
        }

        return $this->renderer
            ->appendContent(sb()
                ->para(sb()->success(sb()->bold(t('Test successful'))))
                ->para(t('The action handling has been cancelled successfully.'))
            );
    }
}
