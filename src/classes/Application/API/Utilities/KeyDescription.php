<?php
/**
 * @package API
 * @subpackage Utilities
 */

declare(strict_types=1);

namespace Application\API\Utilities;

use Application\API\APIManager;
use Application\MarkdownRenderer;

/**
 * Utility class used to describe an API response key with its path and a description.
 *
 * @package API
 * @subpackage Utilities
 */
class KeyDescription implements KeyPathInterface
{
    public KeyPath $path;
    public string $description;

    /**
     * @param KeyPath $path
     * @param string $description Markdown-enabled description of the key.
     * @param mixed ...$args Optional arguments to be used with sprintf to format the description.
     */
    public function __construct(KeyPath $path, string $description, ...$args)
    {
        $this->path = $path;
        $this->description = sprintf($description, ...$args);
    }

    /**
     * @param string|KeyPath $path
     * @param string $description Markdown-enabled description of the key.
     * @param mixed ...$args Optional arguments to be used with sprintf to format the description.
     * @return self
     */
    public static function create(string|KeyPath $path, string $description, ...$args): self
    {
        return new self(KeyPath::create($path), $description, ...$args);
    }

    public function getPath(): string
    {
        return $this->path->getPath();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function renderDescription() : string
    {
        return MarkdownRenderer::create()->renderInline(APIManager::getInstance()->markdownifyMethodNames($this->getDescription()));
    }

    public function __toString() : string
    {
        return $this->getPath().': '.$this->description;
    }
}
