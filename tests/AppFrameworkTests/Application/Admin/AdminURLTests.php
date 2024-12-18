<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application\Admin;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLException;

final class AdminURLTests extends ApplicationTestCase
{
    public function test_importURLParameters() : void
    {
        $url = AdminURL::create()->importURL(APP_URL.'?foo=bar&argh=lopos');

        $this->assertStringContainsString('foo=bar', (string)$url);
        $this->assertStringContainsString('argh=lopos', (string)$url);
        $this->assertSame(
            array('argh' => 'lopos', 'foo' => 'bar'),
            $url->getParams()
        );
    }

    public function test_importEmptyDispatcher() : void
    {
        // Duplicate slashes are ignored when importing the dispatcher
        $url = APP_URL.'/////';

        $this->assertSame('', AdminURL::create()->importURL($url)->getDispatcher());
    }

    public function test_importURLDispatcherScript() : void
    {
        $url = APP_URL.'/dispatcher.php?foo=bar&argh=lopos';

        $this->assertSame('dispatcher.php', AdminURL::create()->importURL($url)->getDispatcher());
    }

    public function test_importURLDispatcherPath() : void
    {
        $url = APP_URL.'/dispatcher/path/?foo=bar&argh=lopos';

        $this->assertSame('dispatcher/path/', AdminURL::create()->importURL($url)->getDispatcher());

        echo AppFactory::createRequest()->buildURL().PHP_EOL;
        echo AdminURL::create()->importURL($url);
    }

    public function test_removeParam() : void
    {
        $params = AdminURL::create(array('foo' => 'bar', 'argh' => 'lopos'))
            ->remove('foo')
            ->getParams();

        $this->assertSame(array('argh' => 'lopos'), $params);
    }

    public function test_otherHostsThrowAnException() : self
    {
        $this->expectExceptionCode(AdminURLException::ERROR_INVALID_HOST);

        AdminURL::create()->importURL('https://example.com/');
    }
}
