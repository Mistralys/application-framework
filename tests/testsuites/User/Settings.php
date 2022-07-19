<?php

use Mistralys\AppFrameworkTests\TestClasses\UserTestCase;

final class User_SettingsTest extends UserTestCase
{
    public function test_setSetting() : void
    {
        $this->logHeader('Set setting');

        $value = 'bar';

        $this->user->setSetting('foo', $value);
        $this->user->saveSettings();

        $this->assertSame($value, $this->user->getSetting('foo'));

        $this->user->clearCache();

        $this->assertSame($value, $this->user->getSetting('foo'));
    }

    public function test_setBooleanSetting() : void
    {
        $this->logHeader('Set boolean');

        $this->user->setBoolSetting('foo', true);
        $this->user->saveSettings();

        $this->assertSame(true, $this->user->getBoolSetting('foo'));

        $this->user->clearCache();

        $this->assertSame(true, $this->user->getBoolSetting('foo'));
    }

    public function test_setDateSetting() : void
    {
        $this->logHeader('Set date');

        $date = new DateTime();
        $value = $date->format(DateTime::RFC3339_EXTENDED);

        $this->user->setDateSetting('foo', $date);
        $this->user->saveSettings();

        $this->assertSame($value, $this->user->getDateSetting('foo')->format(DateTime::RFC3339_EXTENDED));

        $this->user->clearCache();

        $this->assertSame($value, $this->user->getDateSetting('foo')->format(DateTime::RFC3339_EXTENDED));
    }

    public function test_setIntSetting() : void
    {
        $this->logHeader('Set integer');

        $value = 45;

        $this->user->setIntSetting('foo', $value);
        $this->user->saveSettings();

        $this->assertSame($value, $this->user->getIntSetting('foo'));

        $this->user->clearCache();

        $this->assertSame($value, $this->user->getIntSetting('foo'));
    }

    public function test_setArraySetting() : void
    {
        $this->logHeader('Set array');

        $value = array(
            'foo' => 'bar',
            'bar' => 45
        );

        $this->user->setArraySetting('foo', $value);
        $this->user->saveSettings();

        $this->assertSame($value, $this->user->getArraySetting('foo'));

        $this->user->clearCache();

        $this->assertSame($value, $this->user->getArraySetting('foo'));
    }
}
