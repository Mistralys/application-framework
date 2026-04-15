<?php
/**
 * @package DeeplHelper
 * @see DeeplHelper
 */

declare(strict_types=1);

use Application\AppFactory;
use Application\ConfigSettings\BaseConfigRegistry;
use DeeplHelper\Admin\DeeplAdminURLs;
use DeeplHelper\DeeplHelperException;
use DeeplXML\Translator;

/**
 * Deepl translation helper, which handles the configuration
 * of the translation layer including the proxy, if enabled.
 *
 * ## Usage
 *
 * Create an instance of the helper using {@see AppFactory::createDeeplHelper()}.
 *
 * ## Configuration
 *
 * The API key and proxy settings can be configured in two ways:
 *
 * 1. Via driver settings (editable at runtime through the application settings UI).
 * 2. Via boot constants (set in the application configuration, used as fallback).
 *
 * Driver settings take precedence over boot constants.
 *
 * @package DeeplHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DeeplHelper
{
    public const string APP_SETTING_API_KEY = 'deepl_api_key';
    public const string APP_SETTING_PROXY_URL = 'deepl_proxy_url';
    public const string APP_SETTING_PROXY_ENABLED = 'deepl_proxy_enabled';

    /**
     * @param Application_Countries_Country $fromCountry
     * @param Application_Countries_Country $toCountry
     * @return Translator
     *
     * @throws Application_Exception
     * @throws DeeplHelperException
     */
    public function createTranslator(Application_Countries_Country $fromCountry, Application_Countries_Country $toCountry) : Translator
    {
        $targetLang = strtoupper($toCountry->getLanguageCode());
        if(isset(Translator::DEPRECATED_TARGET_LANGUAGES[$targetLang])) {
            $targetLang = str_replace('_', '-', strtoupper($toCountry->getLocaleCode()));
        }

        $translator = new Translator(
            $this->requireAPIKey(),
            $fromCountry->getLanguageCode(),
            $targetLang
        );

        if($this->isProxyEnabled())
        {
            $translator->setProxy($this->requireProxyURL());
        }

        return $translator;
    }

    public function getAPIKey() : ?string
    {
        $settings = Application_Driver::getInstance()->getSettings();

        $key = $settings->get(self::APP_SETTING_API_KEY);
        if(!empty($key)) {
            return $key;
        }

        $key = boot_constant(BaseConfigRegistry::DEEPL_API_KEY);
        if(!empty($key) && is_string($key)) {
            return $key;
        }

        return null;
    }

    /**
     * @return string
     * @throws DeeplHelperException
     */
    public function requireAPIKey() : string
    {
        $key = $this->getAPIKey();

        if($key !== null)
        {
            return $key;
        }

        throw new DeeplHelperException(
            'Missing DeepL API key',
            sprintf(
                'Neither the driver setting [%s] nor the configuration constant [%s] is defined.',
                self::APP_SETTING_API_KEY,
                BaseConfigRegistry::DEEPL_API_KEY
            ),
            DeeplHelperException::ERROR_DEEPL_API_KEY_NOT_SET
        );
    }

    public function isProxyEnabled() : bool
    {
        $settings = Application_Driver::getInstance()->getSettings();

        if($settings->exists(self::APP_SETTING_PROXY_ENABLED)) {
            return $settings->getBool(self::APP_SETTING_PROXY_ENABLED);
        }

        return boot_constant(BaseConfigRegistry::DEEPL_PROXY_ENABLED) === true;
    }

    public function getProxyURL() : ?string
    {
        $settings = Application_Driver::getInstance()->getSettings();

        $url = $settings->get(self::APP_SETTING_PROXY_URL);
        if(!empty($url)) {
            return $url;
        }

        $url = boot_constant(BaseConfigRegistry::DEEPL_PROXY_URL);
        if(!empty($url) && is_string($url)) {
            return $url;
        }

        return null;
    }

    /**
     * @return string
     * @throws DeeplHelperException
     */
    public function requireProxyURL() : string
    {
        $url = $this->getProxyURL();

        if($url !== null)
        {
            return $url;
        }

        throw new DeeplHelperException(
            'The DeepL proxy is enabled, but no proxy URI has been specified.',
            '',
            DeeplHelperException::ERROR_DEEPL_PROXY_URL_EMPTY
        );
    }

    private ?DeeplAdminURLs $adminURLs = null;

    public function adminURL() : DeeplAdminURLs
    {
        if(!isset($this->adminURLs))
        {
            $this->adminURLs = new DeeplAdminURLs();
        }

        return $this->adminURLs;
    }
}
