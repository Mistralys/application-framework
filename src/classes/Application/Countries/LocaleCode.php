<?php

declare(strict_types=1);

class Application_Countries_LocaleCode
{
    const ERROR_CODE_CANNOT_BE_PARSED = 87701;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $countryISO;

    /**
     * @var string
     */
    private $languageCode;

    /**
     * @param string $code Locale code, e.g. "de_DE"
     * @throws Application_Countries_Exception
     *
     * @see Application_Countries_LocaleCode::ERROR_CODE_CANNOT_BE_PARSED
     */
    public function __construct(string $code)
    {
        $this->code = $code;

        $this->parse();
    }

    /**
     * @throws Application_Countries_Exception
     */
    private function parse() : void
    {
        $parts = explode('_', strtolower($this->code));

        if($parts !== false && count($parts) == 2) {
            $this->countryISO = $parts[1];
            $this->languageCode = $parts[0];
            return;
        }

        throw new Application_Countries_Exception(
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
