# API Cache - Cache Strategies (Public API)
_SOURCE: FixedDurationStrategy, ManualOnlyStrategy_
# FixedDurationStrategy, ManualOnlyStrategy
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Cache/
                    └── Strategies/
                        └── FixedDurationStrategy.php
                        └── ManualOnlyStrategy.php

```
###  Path: `/src/classes/Application/API/Cache/Strategies/FixedDurationStrategy.php`

```php
namespace Application\API\Cache\Strategies;

use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\API\Cache\APICacheStrategyInterface as APICacheStrategyInterface;

/**
 * Cache strategy that considers a cache file valid as long as its modification
 * time is within the configured duration from the current time.
 *
 * @package API
 * @subpackage Cache
 */
class FixedDurationStrategy implements APICacheStrategyInterface
{
	public const STRATEGY_ID = 'FixedDuration';
	public const DURATION_1MIN = 60;
	public const DURATION_5MIN = 300;
	public const DURATION_15MIN = 900;
	public const DURATION_1HOUR = 3600;
	public const DURATION_6HOURS = 21600;
	public const DURATION_12HOURS = 43200;
	public const DURATION_24HOURS = 86400;

	public function getID(): string
	{
		/* ... */
	}


	/**
	 * Returns true if the cache file's modification time is within
	 * the configured duration from the current time.
	 * Returns false if `filemtime()` returns false (e.g. race condition
	 * during parallel deletion — treated as expired).
	 *
	 * @param JSONFile $cacheFile
	 * @return bool
	 */
	public function isCacheFileValid(JSONFile $cacheFile): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Cache/Strategies/ManualOnlyStrategy.php`

```php
namespace Application\API\Cache\Strategies;

use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\API\Cache\APICacheStrategyInterface as APICacheStrategyInterface;

/**
 * Cache strategy that never expires a cache file automatically.
 * Cache entries are only invalidated through explicit calls to
 * {@see CacheableAPIMethodTrait::invalidateCache()}.
 *
 * @package API
 * @subpackage Cache
 */
class ManualOnlyStrategy implements APICacheStrategyInterface
{
	public const STRATEGY_ID = 'ManualOnly';

	public function getID(): string
	{
		/* ... */
	}


	public function isCacheFileValid(JSONFile $cacheFile): bool
	{
		/* ... */
	}
}


```