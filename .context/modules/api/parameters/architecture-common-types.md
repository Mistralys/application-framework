# API Parameters - Common Types (Public API)
_SOURCE: AliasParameter, AlphabeticalParameter, AlphanumericParameter, DateParameter, EmailParameter, LabelParameter, MD5Parameter, NameOrTitleParameter_
# AliasParameter, AlphabeticalParameter, AlphanumericParameter, DateParameter, EmailParameter, LabelParameter, MD5Parameter, NameOrTitleParameter
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Parameters/
                    └── CommonTypes/
                        └── AliasParameter.php
                        └── AlphabeticalParameter.php
                        └── AlphanumericParameter.php
                        └── DateParameter.php
                        └── EmailParameter.php
                        └── LabelParameter.php
                        └── MD5Parameter.php
                        └── NameOrTitleParameter.php

```
###  Path: `/src/classes/Application/API/Parameters/CommonTypes/AliasParameter.php`

```php
namespace Application\API\Parameters\CommonTypes;

use Application\API\Parameters\Type\StringParameter as StringParameter;

/**
 * Parameter for an alias validated according to {@see StringValidations::alias()}.
 *
 * @package API
 * @subpackage Parameters
 */
class AliasParameter extends StringParameter
{
	public function getAlias(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/CommonTypes/AlphabeticalParameter.php`

```php
namespace Application\API\Parameters\CommonTypes;

use Application\API\Parameters\Type\StringParameter as StringParameter;

/**
 * Parameter for an alphabetical string validated according to {@see StringValidations::alphabetical()}.
 *
 * @package API
 * @subpackage Parameters
 */
class AlphabeticalParameter extends StringParameter
{
	public function getAlphabetical(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/CommonTypes/AlphanumericParameter.php`

```php
namespace Application\API\Parameters\CommonTypes;

use Application\API\Parameters\Type\StringParameter as StringParameter;

/**
 * Parameter for an alphanumeric string validated according to {@see StringValidations::alphanumeric()}.
 *
 * @package API
 * @subpackage Parameters
 */
class AlphanumericParameter extends StringParameter
{
	public function getAlphanumeric(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/CommonTypes/DateParameter.php`

```php
namespace Application\API\Parameters\CommonTypes;

use AppUtils\Microtime as Microtime;
use Application\API\Parameters\Type\StringParameter as StringParameter;

/**
 * Parameter for a date string, with or without time.
 *
 * @package API
 * @subpackage Parameters
 */
class DateParameter extends StringParameter
{
	public function getDate(): ?Microtime
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/CommonTypes/EmailParameter.php`

```php
namespace Application\API\Parameters\CommonTypes;

use Application\API\Parameters\Type\StringParam\StringValidations as StringValidations;
use Application\API\Parameters\Type\StringParameter as StringParameter;

/**
 * Parameter for an email address according to {@see StringValidations::email()}.
 *
 * @package API
 * @subpackage Parameters
 */
class EmailParameter extends StringParameter
{
	public function getEmail(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/CommonTypes/LabelParameter.php`

```php
namespace Application\API\Parameters\CommonTypes;

use Application\API\Parameters\Type\StringParameter as StringParameter;

/**
 * Parameter for a label validated according to {@see StringValidations::label()}.
 *
 * @package API
 * @subpackage Parameters
 */
class LabelParameter extends StringParameter
{
	public function getLabelValue(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/CommonTypes/MD5Parameter.php`

```php
namespace Application\API\Parameters\CommonTypes;

use Application\API\Parameters\Type\StringParameter as StringParameter;

/**
 * Parameter for an MD5 hash.
 *
 * @package API
 * @subpackage Parameters
 */
class MD5Parameter extends StringParameter
{
	public function getMD5(): ?string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Parameters/CommonTypes/NameOrTitleParameter.php`

```php
namespace Application\API\Parameters\CommonTypes;

use Application\API\Parameters\Type\StringParam\StringValidations as StringValidations;
use Application\API\Parameters\Type\StringParameter as StringParameter;

/**
 * Parameter for a name or title validated according to {@see StringValidations::nameOrTitle()}.
 *
 * @package API
 * @subpackage Parameters
 */
class NameOrTitleParameter extends StringParameter
{
	public function getNameOrTitle(): ?string
	{
		/* ... */
	}
}


```