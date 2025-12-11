<?php
/**
 * @package Application
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\WhatsNew\Admin\Screens;

use Application\Admin\Area\BaseMode;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\AppFactory;
use Application\WhatsNew\Admin\WhatsNewScreenRights;
use AppUtils\FileHelper\FolderInfo;

/**
 * User interface for editing the `WHATSNEW.xml` file.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class WhatsNewEditorMode extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'whatsneweditor';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return WhatsNewScreenRights::SCREEN_MAIN;
    }

    public function getDefaultSubmode(): string
    {
        return ListSubmode::URL_NAME;
    }

    public function getDefaultSubscreenClass(): string
    {
        return ListSubmode::class;
    }

    public function getNavigationTitle(): string
    {
        return t('What\'s new editor');
    }

    public function getDevCategory(): string
    {
        return t('Tools');
    }

    public function getTitle(): string
    {
        return t('What\'s new editor');
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->getTitle());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);

        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked(AppFactory::createWhatsNew()->getAdminListURL());
    }
}
