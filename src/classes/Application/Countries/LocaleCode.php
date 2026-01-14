<?php

declare(strict_types=1);

use Application\Countries\CountryException;
use AppUtils\ConvertHelper;

class Application_Countries_LocaleCode
{
    public const int ERROR_CODE_CANNOT_BE_PARSED = 87701;

    private string $code;
    private string $countryISO;
    private string $languageCode;

    /**
     * @param string $code Locale code, e.g. "de_DE"
     * @throws CountryException
     *
     * @see Application_Countries_LocaleCode::ERROR_CODE_CANNOT_BE_PARSED
     */
    public function __construct(string $code)
    {
        $this->code = $code;

        $this->parse();
    }

    /**
     * @throws CountryException
     */
    private function parse() : void
    {
        $parts = ConvertHelper::explodeTrim('_', strtolower($this->code));

        if(count($parts) === 2) {
            $this->countryISO = $parts[1];
            $this->languageCode = $parts[0];
            return;
        }

        throw new CountryException(
            'Invalid locale code',
            sprintf(
                'The locale code [%s] cannot be parsed.',
                $this->code
            ),
            self::ERROR_CODE_CANNOT_BE_PARSED
        );
    }

    public function getCode() : string
    {
        return $this->code;
    }

    public function getCountryISO() : string
    {
        return $this->countryISO;
    }

    public function getLanguageCode() : string
    {
        return $this->languageCode;
    }

    public function getCountry() : Application_Countries_Country
    {
        return Application_Countries::getInstance()->getByISO($this->getCountryISO());
    }
}
