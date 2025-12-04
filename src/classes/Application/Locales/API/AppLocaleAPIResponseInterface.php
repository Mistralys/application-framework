<?php

declare(strict_types=1);

namespace Application\Locales\API;

interface AppLocaleAPIResponseInterface
{
    public const string KEY_LOCALE_ID = 'localeID';
    public const string KEY_LOCALE_COUNTRY_CODE = 'countryCode';
    public const string KEY_ROOT_LOCALES = 'locales';
    public const string KEY_LOCALE_LANGUAGE_CODE = 'languageCode';
    public const string KEY_LOCALE_LABEL = 'label';
    public const string KEY_LOCALE_LABEL_INVARIANT = 'labelInvariant';
}
