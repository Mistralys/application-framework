<?php

declare(strict_types=1);

return array (
  'events' => 
  array (
    'DisplayAppConfig' => 'Application\\Development\\Events\\DisplayAppConfigEvent',
    'PriorityTest' => 'TestDriver\\OfflineEvents\\PriorityTestEvent',
    'RegisterAppSettings' => 'Application\\AppSettings\\Events\\RegisterAppSettingsEvent',
    'RegisterCacheLocations' => 'Application\\CacheControl\\Events\\RegisterCacheLocationsEvent',
    'RegisterTagCollections' => 'Application\\Tags\\Events\\RegisterTagCollectionsEvent',
    'SessionInstantiated' => 'Application\\Session\\Events\\SessionInstantiatedEvent',
    'Test' => 'TestDriver\\OfflineEvents\\TestEvent',
    'WelcomeQuickNav' => 'Application\\Admin\\Welcome\\Events\\WelcomeQuickNavEvent',
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
      0 => 'Application\\API\\Events\\RegisterAPIIndexCacheListener',
      1 => 'Application\\OfflineEvents\\RegisterCacheLocationsEvent\\RegisterClassCacheListener',
    ),
    'RegisterTagCollections' => 
    array (
      0 => 'Application\\Media\\Events\\RegisterMediaTagsListener',
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
