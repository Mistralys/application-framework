<?php
/**
 * File containing the class {@see \Application\Admin\Area\Mode\Submode\AppVersionEditSubmode}.
 *
 * @package Application
 * @subpackage Admin
 * @see \Application\Admin\Area\Mode\Submode\AppVersionEditSubmode
 */

declare(strict_types=1);

namespace Application\Admin\Area\Mode\Submode;

use Application\WhatsNew;
use Application\WhatsNew\AppVersion;
use Application\WhatsNew\AppVersion\VersionLanguage;
use Application_Admin_Area_Mode_Submode;
use Application_Driver;
use function t;

/**
 * Base class for screens that edit an application version.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class AppVersionEditSubmode extends Application_Admin_Area_Mode_Submode
{
    protected WhatsNew $whatsNew;
    protected AppVersion $version;
    protected string $activeLanguageID;
    protected VersionLanguage $activeLanguage;

    public function isUserAllowed(): bool
    {
        return $this->user->isDeveloper();
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    protected function _handleActions(): bool
    {
        $this->whatsNew = Application_Driver::createWhatsnew();

        $this->resolveVersion();
        $this->resolveActiveLanguage();

        return true;
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setSubtitle(t('v%1$s', $this->version->getNumber()));
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem(t('v%1$s', $this->version->getNumber()))
            ->makeLinked($this->version->getAdminEditURL());

        $this->breadcrumb->appendItem($this->activeLanguageID)
            ->makeLinked($this->version->getAdminLanguageURL($this->activeLanguageID));
    }

    protected function _handleTabs(): void
    {
        $languages = VersionLanguage::getLanguageIDs();

        foreach($languages as $language)
        {
            $this->tabs->appendTab($language, 'lang-'.$language)
                ->makeLinked($this->version->getAdminLanguageURL($language));
        }

        $this->tabs->selectTab($this->tabs->getTabByName('lang-'.$this->activeLanguageID));
    }

    private function resolveActiveLanguage() : void
    {
        $languages = VersionLanguage::getLanguageIDs();

        $lang = (string)Application_Driver::getInstance()
            ->getRequest()
            ->registerParam(AppVersion::REQUEST_PARAM_LANG_ID)
            ->setEnum($languages)
            ->get();

        if(empty($lang))
        {
            $lang = array_shift($languages);
        }

        $this->activeLanguageID = $lang;

        if(!$this->version->hasLanguage($lang))
        {
            $this->version->addLanguage($lang);
        }

        $this->activeLanguage = $this->version->getLanguage($lang);
    }

    private function resolveVersion(): void
    {
        $version = $this->whatsNew->getByRequest();

        if ($version === null) {
            $this->redirectWithErrorMessage(
                t('Version number not found.'),
                $this->whatsNew->getAdminListURL()
            );
        }

        $this->version = $version;
    }
}
