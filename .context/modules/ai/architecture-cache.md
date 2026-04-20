# AI Module - Cache Architecture
_SOURCE: Cache strategy interface, base class, concrete strategies, and cache location_
# Cache strategy interface, base class, concrete strategies, and cache location
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── AI/
                └── Cache/
                    └── AICacheLocation.php
                    └── AICacheStrategyInterface.php
                    └── BaseAICacheStrategy.php
                    └── Events/
                        ├── RegisterAIIndexCacheListener.php
                    └── Strategies/
                        └── FixedDurationStrategy.php
                        └── UncachedStrategy.php

```
###  Path: `/src/classes/Application/AI/Cache/AICacheLocation.php`

```php
namespace Application\AI\Cache;

use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use Application\AI\Cache\Strategies\FixedDurationStrategy as FixedDurationStrategy;
use Application\CacheControl\BaseCacheLocation as BaseCacheLocation;

class AICacheLocation extends BaseCacheLocation
{
	public const LOCATION_ID = 'ai_tools_cache';

	public static function getInstance(): self
	{
		/* ... */
	}


	public function getByteSize(): int
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function clear(): void
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AI/Cache/AICacheStrategyInterface.php`

```php
namespace Application\AI\Cache;

use AppUtils\Interfaces\StringPrimaryRecordInterface as StringPrimaryRecordInterface;
use Application\AI\Tools\AIToolInterface as AIToolInterface;

interface AICacheStrategyInterface extends StringPrimaryRecordInterface
{
	public function isCacheValid(AIToolInterface $tool): bool;


	/**
	 * @param AIToolInterface $tool
	 * @return array<int|string,mixed>
	 */
	public function getFromCache(AIToolInterface $tool): array;


	/**
	 * @param AIToolInterface $tool
	 * @param array<int|string,mixed> $data
	 * @return self
	 */
	public function saveCache(AIToolInterface $tool, array $data): self;
}


```
###  Path: `/src/classes/Application/AI/Cache/BaseAICacheStrategy.php`

```php
namespace Application\AI\Cache;

use AppUtils\FileHelper\FolderInfo as FolderInfo;
use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\AI\Tools\AIToolInterface as AIToolInterface;
use Application\Application as Application;

abstract class BaseAICacheStrategy implements AICacheStrategyInterface
{
	public function getCacheFolder(): FolderInfo
	{
		/* ... */
	}


	public function getToolCacheFile(AIToolInterface $tool): JSONFile
	{
		/* ... */
	}


	public function isCacheValid(AIToolInterface $tool): bool
	{
		/* ... */
	}


	public function getFromCache(AIToolInterface $tool): array
	{
		/* ... */
	}


	public function saveCache(AIToolInterface $tool, array $data): AICacheStrategyInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AI/Cache/Events/RegisterAIIndexCacheListener.php`

```php
namespace Application\AI\Cache\Events;

use Application\AI\Cache\AICacheLocation as AICacheLocation;
use Application\API\Collection\APIMethodIndex as APIMethodIndex;
use Application\CacheControl\Events\BaseRegisterCacheLocationsListener as BaseRegisterCacheLocationsListener;

/**
 * Registers the API method index cache location.
 *
 * @package Application
 * @subpackage CacheControl
 *
 * @see APIMethodIndex::getCacheLocation()
 */
class RegisterAIIndexCacheListener extends BaseRegisterCacheLocationsListener
{
}


```
###  Path: `/src/classes/Application/AI/Cache/Events/RegisterAIIndexCacheListener.php`

```php
namespace Application\AI\Cache\Events;

use Application\AI\Cache\AICacheLocation as AICacheLocation;
use Application\API\Collection\APIMethodIndex as APIMethodIndex;
use Application\CacheControl\Events\BaseRegisterCacheLocationsListener as BaseRegisterCacheLocationsListener;

/**
 * Registers the API method index cache location.
 *
 * @package Application
 * @subpackage CacheControl
 *
 * @see APIMethodIndex::getCacheLocation()
 */
class RegisterAIIndexCacheListener extends BaseRegisterCacheLocationsListener
{
}


```
###  Path: `/src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php`

```php
namespace Application\AI\Cache\Strategies;

use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\AI\Cache\BaseAICacheStrategy as BaseAICacheStrategy;

class FixedDurationStrategy extends BaseAICacheStrategy
{
	public const STRATEGY_ID = 'FixedDuration';
	public const DURATION_1_MIN = 60;
	public const DURATION_5_MIN = 300;
	public const DURATION_15_MIN = 900;
	public const DURATION_1_HOUR = 3600;
	public const DURATION_6_HOURS = 21600;
	public const DURATION_12_HOURS = 43200;
	public const DURATION_24_HOURS = 86400;

	public function getID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AI/Cache/Strategies/FixedDurationStrategy.php`

```php
namespace Application\AI\Cache\Strategies;

use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\AI\Cache\BaseAICacheStrategy as BaseAICacheStrategy;

class FixedDurationStrategy extends BaseAICacheStrategy
{
	public const STRATEGY_ID = 'FixedDuration';
	public const DURATION_1_MIN = 60;
	public const DURATION_5_MIN = 300;
	public const DURATION_15_MIN = 900;
	public const DURATION_1_HOUR = 3600;
	public const DURATION_6_HOURS = 21600;
	public const DURATION_12_HOURS = 43200;
	public const DURATION_24_HOURS = 86400;

	public function getID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AI/Cache/Strategies/UncachedStrategy.php`

```php
namespace Application\AI\Cache\Strategies;

use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\AI\Cache\BaseAICacheStrategy as BaseAICacheStrategy;

class UncachedStrategy extends BaseAICacheStrategy
{
	public const STRATEGY_ID = 'Uncached';

	public function getID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AI/Cache/Strategies/UncachedStrategy.php`

```php
namespace Application\AI\Cache\Strategies;

use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\AI\Cache\BaseAICacheStrategy as BaseAICacheStrategy;

class UncachedStrategy extends BaseAICacheStrategy
{
	public const STRATEGY_ID = 'Uncached';

	public function getID(): string
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 6.41 KB
- **Lines**: 286
File: `modules/ai/architecture-cache.md`
