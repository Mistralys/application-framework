<?php

declare(strict_types=1);

use AppFrameworkTestClasses\ApplicationTestCase;

final class Rating_URLTests extends ApplicationTestCase
{
    /**
     * Ensure that parsing application URLs works as intended,
     * giving the expected request hashes.
     */
    public function test_hashes()
    {
        $tests = array(
            array(
                'label' => 'Without path or parameters',
                'url' => 'http://spin.de',
                'params' => array(),
                'dispatcher' => '',
                'screen' => ''
            ),
            array(
                'label' => 'Without path or parameters, ending slash',
                'url' => 'http://spin.de/',
                'params' => array(),
                'dispatcher' => '',
                'screen' => ''
            ),
            array(
                'label' => 'With subfolder dispatcher',
                'url' => 'http://spin.de/export/index.php',
                'params' => array(),
                'dispatcher' => 'export',
                'screen' => ''
            ),
            array(
                'label' => 'With other dispatcher',
                'url' => 'http://spin.de/upgrade.php',
                'params' => array(),
                'dispatcher' => 'upgrade.php',
                'screen' => ''
            ),
            array(
                'label' => 'With page parameter and dispatcher',
                'url' => 'http://spin.de/index.php?page=home',
                'params' => array(),
                'dispatcher' => '',
                'screen' => 'home'
            ),
            array(
                'label' => 'With page parameter and without dispatcher',
                'url' => 'http://spin.de/?page=home',
                'params' => array(),
                'dispatcher' => '',
                'screen' => 'home'
            ),
            array(
                'label' => 'Several subfolders, and hash',
                'url' => 'http://spin.de/xml/monitor/index.php#jump',
                'params' => array(),
                'dispatcher' => 'xml/monitor',
                'screen' => ''
            ),
            array(
                'label' => 'Full screen path, no other params',
                'url' => 'http://spin.de/?page=page&mode=mode&submode=submode&action=action',
                'params' => array(),
                'dispatcher' => '',
                'screen' => 'page.mode.submode.action'
            ),
            array(
                'label' => 'Full screen path and other params',
                'url' => 'http://spin.de/?page=page&mode=mode&test=foo&submode=submode&action=action&bar=argh',
                'params' => array(
                    'bar' => 'argh',
                    'test' => 'foo'
                ),
                'dispatcher' => '',
                'screen' => 'page.mode.submode.action'
            ),
            array(
                'label' => 'Empty parameters',
                'url' => 'http://spin.de/?&param=',
                'params' => array(
                    'param' => ''
                ),
                'dispatcher' => '',
                'screen' => ''
            )
        );

        $driver = Application_Driver::getInstance();

        foreach($tests as $test)
        {
            $info = $driver->parseURL($test['url']);

            $this->assertEquals($test['params'], $info->getParams());
            $this->assertEquals($test['screen'], $info->getScreenPath());
            $this->assertEquals($test['dispatcher'], $info->getDispatcher());
        }
    }
}
