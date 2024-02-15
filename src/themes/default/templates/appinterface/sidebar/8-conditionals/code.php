<?php

declare(strict_types=1);

/** @var string $activeURL */

use Application\AppFactory;

// -----------------------------------------------------------
// Update and get example setting
// -----------------------------------------------------------

$request = AppFactory::createRequest();
$driver = AppFactory::createDriver();
$settings = $driver->getSettings();

if($request->hasParam('enable'))
{
    $value = $request->getBool('enable');
    $settings->setBool('example-setting', $value);

    $driver->redirectWithSuccessMessage(
        sprintf(
            'Setting changed to %1$s',
            UI::prettyBool($value)->makeEnabledDisabled()
        ),
        $activeURL
    );
}

$enabled = $settings->getBool('example-setting');

// -----------------------------------------------------------
// Sidebar
// -----------------------------------------------------------

$sidebar = new UI_Page_Sidebar('sidebar-example');

$sidebar->addButton('btn-enable', 'Enable setting')
    ->makeSuccess()
    ->setIcon(UI::icon()->on())
    ->requireFalse($enabled)
    ->link($activeURL.'&enable=yes');

$sidebar->addButton('btn-disable', 'Disable setting')
    ->makeWarning()
    ->setIcon(UI::icon()->off())
    ->requireTrue($enabled)
    ->link($activeURL.'&enable=no');

echo $sidebar;

