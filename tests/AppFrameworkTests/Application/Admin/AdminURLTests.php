<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application\Admin;

use AppFrameworkTestClasses\ApplicationTestCase;
use UI\AdminURLs\AdminURL;

final class AdminURLTests extends ApplicationTestCase
{
    public function test_importURLParameters() : void
    {
        $url = AdminURL::create()->importURL('https://www.example.com?foo=bar&argh=lopos');

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
        $url = 'https://www.example.com/////';

        $this->assertSame('', AdminURL::create()->importURL($url)->getDispatcher());
    }

    public function test_importURLDispatcherScript() : void
    {
        $url = 'https://www.example.com/dispatcher.php?foo=bar&argh=lopos';

        $this->assertSame('dispatcher.php', AdminURL::create()->importURL($url)->getDispatcher());
    }

    public function test_importURLDispatcherPath() : void
    {
        $url = 'https://www.example.com/dispatcher/path/?foo=bar&argh=lopos';

        $this->assertSame('dispatcher/path/', AdminURL::create()->importURL($url)->getDispatcher());
    }

    public function test_removeParam() : void
    {
        $params = AdminURL::create(array('foo' => 'bar', 'argh' => 'lopos'))
            ->remove('foo')
            ->getParams();

        $this->assertSame(array('argh' => 'lopos'), $params);
    }
}
