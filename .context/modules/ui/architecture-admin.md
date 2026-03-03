# UI Module - Admin Screens Architecture
_SOURCE: Admin screen class signatures_
# Admin screen class signatures
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── Admin/
                └── Screens/
                    └── AppInterfaceDevelMode.php
                    └── CSSGenDevelMode.php

```
###  Path: `/src/classes/UI/Admin/Screens/AppInterfaceDevelMode.php`

```php
namespace UI\Admin\Screens;

use Application\Admin\Area\BaseMode as BaseMode;
use Application\Admin\Traits\DevelModeInterface as DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait as DevelModeTrait;
use Application\Development\Admin\DevScreenRights as DevScreenRights;
use Application\Themes\DefaultTemplate\devel\appinterface\ExampleOverviewTemplate as ExampleOverviewTemplate;
use Application\Themes\DefaultTemplate\devel\appinterface\ExampleTemplate as ExampleTemplate;
use Mistralys\Examples\InterfaceExamples as InterfaceExamples;
use Mistralys\Examples\UserInterface\ExampleFile as ExampleFile;
use Mistralys\Examples\UserInterface\ExamplesCategory as ExamplesCategory;
use UI_Themes_Theme_ContentRenderer as UI_Themes_Theme_ContentRenderer;

/**
 * Abstract base class used to display a reference of application UI
 * elements that can be used when building administration screens.
 * It creates a live menu to choose which examples to show.
 *
 * The examples themselves are stored as templates, under
 * `templates/appinterface`, with folders corresponding to the
 * example's category ID.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class AppInterfaceDevelMode extends BaseMode implements DevelModeInterface
{
	use DevelModeTrait;

	public const URL_NAME = 'appinterface';
	public const TEMPLATE_VAR_EXAMPLES = 'examples';
	public const TEMPLATE_VAR_ACTIVE_ID = 'active';
	public const REQUEST_PARAM_EXAMPLE_ID = 'example';
	public const TEMPLATE_VAR_CATEGORIES = 'categories';

	public function getURLName(): string
	{
		/* ... */
	}


	public function getRequiredRight(): string
	{
		/* ... */
	}


	public function getDefaultSubscreenClass(): null
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	public function getDevCategory(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/Admin/Screens/CSSGenDevelMode.php`

```php
namespace UI\Admin\Screens;

use AppUtils\OutputBuffering as OutputBuffering;
use Application\Admin\Area\BaseMode as BaseMode;
use Application\Admin\Traits\DevelModeInterface as DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait as DevelModeTrait;
use Application\Development\Admin\DevScreenRights as DevScreenRights;
use UI as UI;
use UI\CSSGenerator\CSSGen as CSSGen;
use UI_DataGrid as UI_DataGrid;
use UI_DataGrid_Action as UI_DataGrid_Action;
use UI_Themes_Theme_ContentRenderer as UI_Themes_Theme_ContentRenderer;

class CSSGenDevelMode extends BaseMode implements DevelModeInterface
{
	use DevelModeTrait;

	public const URL_NAME = 'css-gen';
	public const COL_NAME = 'name';
	public const COL_LOCATION = 'location';
	public const COL_FILE_ID = 'file_id';
	public const COL_STATUS = 'status';
	public const COL_PATH = 'path';
	public const REQUEST_PARAM_GENERATE_ALL = 'generate';

	public function getURLName(): string
	{
		/* ... */
	}


	public function getRequiredRight(): string
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getDevCategory(): string
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 3.7 KB
- **Lines**: 157
File: `modules/ui/architecture-admin.md`
