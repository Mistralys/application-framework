<?php

declare(strict_types=1);

return array (
  'events' => 
  array (
    'DisplayAppConfig' => 'Application\\OfflineEvents\\DisplayAppConfigEvent',
    'PriorityTest' => 'TestDriver\\OfflineEvents\\PriorityTestEvent',
    'RegisterAppSettings' => 'Application\\OfflineEvents\\RegisterAppSettingsEvent',
    'RegisterCacheLocations' => 'Application\\OfflineEvents\\RegisterCacheLocationsEvent',
    'RegisterTagCollections' => 'Application\\OfflineEvents\\RegisterTagCollectionsEvent',
    'SessionInstantiated' => 'Application\\OfflineEvents\\SessionInstantiatedEvent',
    'Test' => 'TestDriver\\OfflineEvents\\TestEvent',
    'WelcomeQuickNav' => 'Application\\OfflineEvents\\WelcomeQuickNavEvent',
  ),
  'listeners' => 
  array (
    'DisplayAppConfig' => 
    array (
      0 => 'TestDriver\\OfflineEvents\\DisplayAppConfig\\DisplayTestConfigListener',
    ),
    'PriorityTest' => 
    array (
      0 => 'TestDriver\\OfflineEvents\\PriorityTest\\PriorityListenerA',
      1 => 'TestDriver\\OfflineEvents\\PriorityTest\\PriorityListenerB',
      2 => 'application\\assets\\classes\\TestDriver\\OfflineEvents\\PriorityTest\\PriorityListenerC',
    ),
    'RegisterAppSettings' => 
    array (
      0 => 'TestDriver\\OfflineEvents\\RegisterAppSettings\\RegisterSettingsListener',
    ),
    'RegisterCacheLocations' => 
    array (
      0 => 'Application\\OfflineEvents\\RegisterCacheLocationsEvent\\RegisterAPIIndexListener',
      1 => 'Application\\OfflineEvents\\RegisterCacheLocationsEvent\\RegisterClassCacheListener',
    ),
    'RegisterTagCollections' => 
    array (
      0 => 'Application\\OfflineEvents\\RegisterTagCollectionsEvent\\RegisterMediaTagsListener',
      1 => 'TestDriver\\OfflineEvents\\RegisterTagCollections\\RegisterTestDBCollection',
    ),
    'SessionInstantiated' => 
    array (
      0 => 'TestDriver\\OfflineEvents\\SessionInstantiated\\TestSessionInstantiatedListener',
    ),
    'Test' => 
    array (
      0 => 'TestDriver\\OfflineEvents\\Test\\ListenerA',
      1 => 'TestDriver\\OfflineEvents\\Test\\ListenerB',
    ),
    'WelcomeQuickNav' => 
    array (
      0 => 'TestDriver\\OfflineEvents\\WelcomeQuickNav\\AddCustomWelcomeItemsListener',
    ),
  ),
);
