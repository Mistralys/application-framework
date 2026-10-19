# API Parameters - Removed Parameters (Public API)
_SOURCE: RemovedParameter, RemovedParamCollection_
# RemovedParameter, RemovedParamCollection
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Parameters/
                    └── Removed/
                        └── RemovedParamCollection.php
                        └── RemovedParameter.php

```
###  Path: `/src/classes/Application/API/Parameters/Removed/RemovedParamCollection.php`

```php
namespace Application\API\Parameters\Removed;

/**
 * Collection of {@see RemovedParameter} declarations for a single
 * API method. Owns storage, name lookups, request-based detection,
 * and serialization for the removed parameters an
 * {@see \Application\API\Parameters\APIParamManager} has registered.
 *
 * @package API
 * @subpackage Parameters
 */
class RemovedParamCollection
{
	public function add(RemovedParameter $param): self
	{
		/* ... */
	}


	public function has(string $name): bool
	{
		/* ... */
	}


	public function isEmpty(): bool
	{
		/* ... */
	}


	/**
	 * @return RemovedParameter[] Sorted by parameter name.
	 */
	public function getAll(): array
	{
		/* ... */
	}


	/**
	 * Detects which of the declared removed parameters are present
	 * in the given request parameters, regardless of their value
	 * (an explicit empty string still counts as "used").
	 *
	 * @param array<string,mixed> $requestParams
	 * @return RemovedParameter[] Sorted by parameter name.
	 */
	public function detectUsed(array $requestParams): array
	{
		/* ... */
	}


	/**
	 * @return array<array<string,string|NULL>>
	 */
	public function toArray(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/Removed/RemovedParameter.php`

```php
namespace Application\API\Parameters\Removed;

use Application\API\Parameters\APIParameterException as APIParameterException;

/**
 * Represents a single API parameter that has been removed from an
 * API method: Its name is still known to the framework so that a
 * request still sending it can be detected and rejected with a
 * helpful error message, instead of the application having to
 * hand-write a guard for it.
 *
 * Instances are created via {@see \Application\API\Parameters\APIParamManager::addRemovedParam()}.
 *
 * @package API
 * @subpackage Parameters
 */
class RemovedParameter
{
	public const KEY_NAME = 'name';
	public const KEY_REPLACEMENT = 'replacement';
	public const KEY_REMOVED_SINCE = 'removedSince';
	public const KEY_NOTE = 'note';

	/**
	 * Characters that PHP's request parameter parsing mangles or drops
	 * (`parse_str()`-style key handling): a `.` or space is converted to
	 * `_`, an unmatched `[` is converted to `_` (or opens an array key),
	 * and a NUL byte truncates the key. A lone `]` is preserved verbatim
	 * by PHP, so it is deliberately not part of this denylist. A
	 * removed-parameter name must not contain any of the denylisted
	 * characters, since {@see \Application\API\Parameters\Removed\RemovedParamCollection::detectUsed()}
	 * compares the declared name against the raw request keys with
	 * `array_key_exists()` - a declared name whose client-sent `.`/space/`[`/NUL
	 * variant can never match would mean the declaration silently never fires.
	 */
	public const INVALID_NAME_CHARACTERS = ". [\x00";

	public function getName(): string
	{
		/* ... */
	}


	/**
	 * Declares the name of the parameter that replaces this removed one,
	 * if any. Used to enrich {@see self::getErrorMessage()}.
	 *
	 * @param string $replacement
	 * @return $this
	 */
	public function setReplacement(string $replacement): self
	{
		/* ... */
	}


	public function getReplacement(): ?string
	{
		/* ... */
	}


	public function hasReplacement(): bool
	{
		/* ... */
	}


	/**
	 * Declares the version since which the parameter has been removed.
	 * Free-form string, purely informational for the error message.
	 *
	 * @param string $version
	 * @return $this
	 */
	public function setRemovedSince(string $version): self
	{
		/* ... */
	}


	public function getRemovedSince(): ?string
	{
		/* ... */
	}


	public function hasRemovedSince(): bool
	{
		/* ... */
	}


	/**
	 * Sets a free-form note to append to the error message, e.g. to
	 * explain the context of the removal. The note is used verbatim:
	 * it is never interpreted as a format string, so literal `%` or
	 * `%s` sequences are safe to use.
	 *
	 * @param string $note
	 * @return $this
	 */
	public function setNote(string $note): self
	{
		/* ... */
	}


	public function getNote(): ?string
	{
		/* ... */
	}


	public function hasNote(): bool
	{
		/* ... */
	}


	/**
	 * Composes the human-readable error message to return to a client
	 * that still sends this removed parameter.
	 *
	 * @return string
	 */
	public function getErrorMessage(): string
	{
		/* ... */
	}


	/**
	 * @return array<string,string|NULL>
	 */
	public function toArray(): array
	{
		/* ... */
	}
}


```