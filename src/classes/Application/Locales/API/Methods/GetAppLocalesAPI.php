<?php

declare(strict_types=1);

namespace Application\Locales\API\Methods;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use Application\Countries\API\Methods\GetAppCountriesAPI;
use Application\Locales;
use Application\Locales\API\AppLocaleAPIInterface;
use Application\Locales\API\AppLocaleAPIResponseInterface;
use Application\Locales\API\AppLocaleAPITrait;
use Application\Locales\API\LocalesAPIGroup;
use AppUtils\ArrayDataCollection;

class GetAppLocalesAPI extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseWithExampleInterface,
    AppLocaleAPIInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use JSONResponseWithExampleTrait;
    use AppLocaleAPITrait;

    public const string METHOD_NAME = 'GetAppLocales';
    public const string VERSION_1_0 = '1.0';
    public const string CURRENT_VERSION = self::VERSION_1_0;
    public const array VERSIONS = array(
        self::VERSION_1_0
    );

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getVersions(): array
    {
        return self::VERSIONS;
    }

    public function getCurrentVersion(): string
    {
        return self::CURRENT_VERSION;
    }

    // region: B - Setup

    protected function init(): void
    {
        $this->registerAppLocaleParameter();
    }

    protected function collectRequestData(string $version): void
    {
    }

    // endregion

    // region: A - Response Payload

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $this->applyLocale();

        $response->setKey(AppLocaleAPIResponseInterface::KEY_ROOT_LOCALES, $this->collectLocales($version));
    }

    private function collectLocales(string $version): array
    {
        $result = array();
        foreach($this->resolveLocales() as $locale) {
            $result[] = $this->collectLocaleData($locale, $version);
        }

        return $result;
    }

    private function collectLocaleData(Locales\Locale $locale, string $version): array
    {
        $loc = $locale->getLocalizationLocale();

        return array(
            AppLocaleAPIResponseInterface::KEY_LOCALE_ID => $locale->getID(),
            AppLocaleAPIResponseInterface::KEY_LOCALE_LANGUAGE_CODE => $locale->getLangISO(),
            AppLocaleAPIResponseInterface::KEY_LOCALE_COUNTRY_CODE => $locale->getCountryISO(),
            AppLocaleAPIResponseInterface::KEY_LOCALE_LABEL => $locale->getLabel(),
            AppLocaleAPIResponseInterface::KEY_LOCALE_LABEL_INVARIANT => $loc->getLabelInvariant()
        );
    }

    /**
     * @return Locales\Locale[]
     */
    private function resolveLocales() : array
    {
        $result = array();

        foreach(Locales::getInstance()->getAll() as $locale) {
            $result[] = $locale;

            if($this->isExampleResponse()) {
                break;
            }
        }

        return $result;
    }

    // endregion

    // region: C - Documentation

    public function getDescription(): string
    {
        return <<<'MARKDOWN'
Retrieves a list of all available application locales 
(for the user interface) along with all relevant details.
MARKDOWN;
    }

    public function getGroup(): APIGroupInterface
    {
        return LocalesAPIGroup::getInstance();
    }

    public function getChangelog(): array
    {
        return array();
    }

    public function getRelatedMethodNames(): array
    {
        return array(
            GetAppCountriesAPI::METHOD_NAME
        );
    }

    public function getReponseKeyDescriptions(): array
    {
        return array();
    }

    // endregion
}
