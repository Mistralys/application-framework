<?php
/**
 * @package Locales
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Locales\API;

use Application\API\Parameters\Type\StringParameter;
use Application\API\Parameters\ValueLookup\SelectableParamValue;
use Application\API\Parameters\ValueLookup\SelectableValueParamInterface;
use Application\API\Parameters\ValueLookup\SelectableValueParamTrait;
use Application\AppFactory;
use AppLocalize\Localization;

/**
 * API parameter for selecting an application locale.
 * The name is {@see AppLocaleAPIInterface::PARAM_LOCALE}.
 *
 * @package Locales
 * @subpackage API
 */
class AppLocaleParam extends StringParameter implements SelectableValueParamInterface
{
    use SelectableValueParamTrait;

    public function __construct()
    {
        parent::__construct(AppLocaleAPIInterface::PARAM_LOCALE, 'Application locale');

        $this
            ->setDescription(sb()
                ->sf('The application locale to use, as used for the %1$s user interface.', AppFactory::createDriver()->getAppNameShort())
                ->add('This determines the language in which translatable text is returned.')
                ->sf('If not provided, the system default locale, %1$s, is used.', sb()->reference(Localization::BUILTIN_LOCALE_NAME))
            )
            ->validateByEnum(Localization::getAppLocaleNames());
    }

    public function getDefaultSelectableValue(): ?SelectableParamValue
    {
        return null;
    }

    protected function _getValues(): array
    {
        $result = array();
        foreach(Localization::getAppLocales() as $appLocale) {
            $result[] = new SelectableParamValue(
                $appLocale->getID(),
                (string)sb()
                    ->add($appLocale->getID())
                    ->add(' - ')
                    ->add($appLocale->getLabelInvariant())
            );
        }

        return $result;
    }
}
