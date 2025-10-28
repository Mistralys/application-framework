<?php

declare(strict_types=1);

namespace AppFrameworkTests\Locales;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\Locales\API\AppLocaleAPIInterface;
use AppLocalize\Localization;
use AppLocalize\Localization\Locale\de_DE;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use TestDriver\API\TestAppLocale\AppLocaleResponse;
use TestDriver\API\TestAppLocaleMethod;

final class AppLocaleAPITests extends APITestCase
{
    // region: _Tests

    public function test_changeLocale() : void
    {
        $this->assertDefaultLocaleSelected();

        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestAppLocaleMethod::METHOD_NAME;
        $_REQUEST[AppLocaleAPIInterface::PARAM_LOCALE] = de_DE::LOCALE_NAME;

        $method = new TestAppLocaleMethod(APIManager::getInstance());

        $this->assertTextIsTranslatedToGerman($method);
    }

    public function test_selectLocaleManually() : void
    {
        $this->assertDefaultLocaleSelected();

        $method = new TestAppLocaleMethod(APIManager::getInstance());

        $method->selectLocale(Localization::getAppLocaleByName(de_DE::LOCALE_NAME));

        $this->assertTextIsTranslatedToGerman($method);
    }

    // endregion

    // region: Support methods

    private function assertTextIsTranslatedToGerman(TestAppLocaleMethod $method) : void
    {
        $response = $this->assertSuccessfulResponse($method);

        $this->assertSame(de_DE::LOCALE_NAME, Localization::getAppLocaleName(), 'The locale was not changed.');

        $this->assertInstanceOf(AppLocaleResponse::class, $response);
        $text = $response->getText();
        $this->assertSame(TestAppLocaleMethod::TEXT_DE, $text, 'The response text is not as expected.');
    }

    // endregion
}
