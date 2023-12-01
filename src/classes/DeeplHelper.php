<?php
/**
 * @package DeeplHelper
 * @see DeeplHelper
 */

declare(strict_types=1);

use Application\ConfigSettings\BaseConfigRegistry;
use DeeplHelper\DeeplHelperException;
use DeeplXML\Translator;

/**
 * Deepl translation helper, which handles the configuration
 * of the translation layer including the proxy, if enabled.
 *
 * @package DeeplHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DeeplHelper
{
    public const ERROR_DEEPL_API_KEY_NOT_SET = 109601;
    public const ERROR_DEEPL_PROXY_URL_EMPTY = 109602;

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
        $translator = new Translator(
            $this->requireAPIKey(),
            $fromCountry->getLanguageCode(),
            $toCountry->getLanguageCode()
        );

        if($this->isProxyEnabled())
        {
            $translator->setProxy($this->requireProxyURL());
        }

        return $translator;
    }

    public function getAPIKey() : ?string
    {
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
                'The configuration setting [%s] is not defined.',
                BaseConfigRegistry::DEEPL_API_KEY
            ),
            self::ERROR_DEEPL_API_KEY_NOT_SET
        );
    }

    public function isProxyEnabled() : bool
    {
        return boot_constant(BaseConfigRegistry::DEEPL_PROXY_ENABLED) === true;
    }

    public function getProxyURL() : ?string
    {
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
            self::ERROR_DEEPL_PROXY_URL_EMPTY
        );
    }
}
