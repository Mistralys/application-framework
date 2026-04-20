# Markdown Renderer - Core Architecture (Public API)
_SOURCE: MarkdownRenderer, BaseCustomTag, and custom tag implementations_
# MarkdownRenderer, BaseCustomTag, and custom tag implementations
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── MarkdownRenderer/
                └── BaseCustomTag.php
                └── CustomTags/
                    ├── APIMethodDocTag.php
                    ├── MediaTag.php
                └── MarkdownRenderer.php

```
###  Path: `/src/classes/Application/MarkdownRenderer/BaseCustomTag.php`

```php
namespace Application\MarkdownRenderer;

use AppUtils\AttributeCollection as AttributeCollection;
use AppUtils\Interfaces\RenderableInterface as RenderableInterface;
use AppUtils\Traits\RenderableTrait as RenderableTrait;

abstract class BaseCustomTag implements RenderableInterface
{
	use RenderableTrait;

	public function getAttributes(): AttributeCollection
	{
		/* ... */
	}


	public function getMatchedText(): string
	{
		/* ... */
	}


	public function getNumber(): int
	{
		/* ... */
	}


	public function getPlaceholder(): string
	{
		/* ... */
	}


	public function getAttribute(string $name): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/MarkdownRenderer/CustomTags/APIMethodDocTag.php`

```php
namespace Application\MarkdownRenderer\CustomTags;

use AppUtils\AttributeCollection as AttributeCollection;
use Application\API\APIManager as APIManager;
use Application\MarkdownRenderer\BaseCustomTag as BaseCustomTag;

/**
 * Detects API Documentation tags:
 *
 * `{api: GetSomething}`
 */
class APIMethodDocTag extends BaseCustomTag
{
	/**
	 * @param string $subject
	 * @return APIMethodDocTag[]
	 */
	public static function findTags(string $subject): array
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function getMethodName(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/MarkdownRenderer/CustomTags/APIMethodDocTag.php`

```php
namespace Application\MarkdownRenderer\CustomTags;

use AppUtils\AttributeCollection as AttributeCollection;
use Application\API\APIManager as APIManager;
use Application\MarkdownRenderer\BaseCustomTag as BaseCustomTag;

/**
 * Detects API Documentation tags:
 *
 * `{api: GetSomething}`
 */
class APIMethodDocTag extends BaseCustomTag
{
	/**
	 * @param string $subject
	 * @return APIMethodDocTag[]
	 */
	public static function findTags(string $subject): array
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function getMethodName(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/MarkdownRenderer/CustomTags/MediaTag.php`

```php
namespace Application\MarkdownRenderer\CustomTags;

use AppUtils\AttributeCollection as AttributeCollection;
use AppUtils\HTMLTag as HTMLTag;
use Application\AppFactory as AppFactory;
use Application\MarkdownRenderer\BaseCustomTag as BaseCustomTag;
use Application\MarkdownRenderer\MarkdownRenderer as MarkdownRenderer;
use Application_Media_Document as Application_Media_Document;
use Application_Media_Document_Image as Application_Media_Document_Image;

/**
 * Detects media tags:
 *
 * <code>{media: 42}</code>
 * <code>{media: 42 width="400"}</code>
 * <code>{media: 42 title="Optional image title attribute"}</code>
 * <code>{media: 42 thumbnail="no"}</code>
 * <code>{media: 42 class="custom-style"}</code>
 */
class MediaTag extends BaseCustomTag
{
	public const DEFAULT_VISUAL_CLASS_NAME = 'visual';

	public function getMediaID(): int
	{
		/* ... */
	}


	/**
	 * @param string $subject
	 * @return MediaTag[]
	 */
	public static function findTags(string $subject): array
	{
		/* ... */
	}


	public function getDocument(): ?Application_Media_Document
	{
		/* ... */
	}


	public function getWidth(): ?int
	{
		/* ... */
	}


	public function getTitle(): ?string
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function getClasses(): array
	{
		/* ... */
	}


	public function isThumbnail(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/MarkdownRenderer/CustomTags/MediaTag.php`

```php
namespace Application\MarkdownRenderer\CustomTags;

use AppUtils\AttributeCollection as AttributeCollection;
use AppUtils\HTMLTag as HTMLTag;
use Application\AppFactory as AppFactory;
use Application\MarkdownRenderer\BaseCustomTag as BaseCustomTag;
use Application\MarkdownRenderer\MarkdownRenderer as MarkdownRenderer;
use Application_Media_Document as Application_Media_Document;
use Application_Media_Document_Image as Application_Media_Document_Image;

/**
 * Detects media tags:
 *
 * <code>{media: 42}</code>
 * <code>{media: 42 width="400"}</code>
 * <code>{media: 42 title="Optional image title attribute"}</code>
 * <code>{media: 42 thumbnail="no"}</code>
 * <code>{media: 42 class="custom-style"}</code>
 */
class MediaTag extends BaseCustomTag
{
	public const DEFAULT_VISUAL_CLASS_NAME = 'visual';

	public function getMediaID(): int
	{
		/* ... */
	}


	/**
	 * @param string $subject
	 * @return MediaTag[]
	 */
	public static function findTags(string $subject): array
	{
		/* ... */
	}


	public function getDocument(): ?Application_Media_Document
	{
		/* ... */
	}


	public function getWidth(): ?int
	{
		/* ... */
	}


	public function getTitle(): ?string
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function getClasses(): array
	{
		/* ... */
	}


	public function isThumbnail(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/MarkdownRenderer/MarkdownRenderer.php`

```php
namespace Application\MarkdownRenderer;

use AppUtils\AttributeCollection as AttributeCollection;
use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\StringBuilder as StringBuilder;
use AppUtils\Traits\OptionableTrait as OptionableTrait;
use Application\MarkdownRenderer\CustomTags\APIMethodDocTag as APIMethodDocTag;
use Application\MarkdownRenderer\CustomTags\MediaTag as MediaTag;
use League\CommonMark\Environment\Environment as Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension as CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension as GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension as HeadingPermalinkExtension;
use League\CommonMark\Extension\Table\TableExtension as TableExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension as TableOfContentsExtension;
use League\CommonMark\MarkdownConverter as MarkdownConverter;
use UI as UI;

class MarkdownRenderer implements OptionableInterface
{
	use OptionableTrait;

	public const OPTION_HTML_INPUT = 'html_input';
	public const OPTION_ALLOW_UNSAFE_LINKS = 'allow_unsafe_links';
	public const HTML_MODE_ALLOW = 'allow';
	public const HTML_MODE_STRIP = 'strip';
	public const HTML_MODE_ESCAPE = 'escape';
	public const WRAPPER_CLASS = 'markdown';
	public const WRAPPER_TAG_OPEN = '<div class="'.self::WRAPPER_CLASS.'">';
	public const WRAPPER_TAG_CLOSE = '</div>';
	public const MARKDOWN_DOCUMENTATION_URL = 'https://commonmark.org/help/';
	public const MARKDOWN_LANGUAGE_NAME = 'Markdown';

	public static function create(): self
	{
		/* ... */
	}


	public static function getName(): string
	{
		/* ... */
	}


	public static function injectReference(?StringBuilder $comment = null, bool $quickRef = false): StringBuilder
	{
		/* ... */
	}


	public function getDefaultOptions(): array
	{
		/* ... */
	}


	public function render(string $markdown): string
	{
		/* ... */
	}


	public static function parseParams(string $params): AttributeCollection
	{
		/* ... */
	}


	/**
	 * @param string $mode
	 * @return $this
	 *
	 * @see self::HTML_MODE_ALLOW
	 * @see self::HTML_MODE_STRIP
	 * @see self::HTML_MODE_ESCAPE
	 */
	public function setHTMLInput(string $mode): self
	{
		/* ... */
	}


	/**
	 * By default, rendering Markdown will return a paragraph-wrapped HTML string.
	 * This method will render the given markdown string without the paragraph tags.
	 *
	 * @param string $getDescription
	 * @return string
	 */
	public function renderInline(string $getDescription): string
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 8.27 KB
- **Lines**: 408
File: `modules/markdown-renderer/architecture-core.md`
