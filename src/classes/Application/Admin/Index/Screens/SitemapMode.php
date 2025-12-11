<?php

declare(strict_types=1);

namespace Application\Admin\Index\Screens;

use Application\Admin\Area\BaseMode;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\Development\Admin\DevScreenRights;

class SitemapMode extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'sitemap';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Sitemap');
    }

    public function getTitle(): string
    {
        return t('Sitemap');
    }

    public function getDevCategory(): string
    {
        return t('Documentation');
    }

    public function getRequiredRight(): string
    {
        return DevScreenRights::SCREEN_SITEMAP;
    }

    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass(): null
    {
        return null;
    }

    /*
     * NOTICE: This non-MFA VPN setup will be discontinued on 31st December 2025.
     * Please use the new profiles with the Suffix'MFA'.SELFADMINS: To be able to
     * use the new Setup make sure you have a suitable up-to-date certificate via
     * vpn.ionos.org and the updated vpn-profile'united-internet-ui.evpn.mfa.xml'
     * aswell as an MFA token enrolled for SSO within you EIAM Account.If you have
     * any questions, please contact Global IT Support or yout local IT-Service
     */
}
