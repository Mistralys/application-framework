<?php
/**
 * @package API
 * @subpackage Versioning
 */

declare(strict_types=1);

namespace Application\API\Versioning;

use Application\API\APIMethodInterface;
use Application\API\Utilities\KeyPath;
use Application\API\Utilities\KeyPathInterface;
use Application\API\Utilities\KeyReplacement;

/**
 * Abstract base class for API versions. Used by API methods that
 * use {@see VersionedAPIInterface} to implement their versioning.
 *
 * @package API
 * @subpackage Versioning
 */
abstract class BaseAPIVersion implements APIVersionInterface
{
    private APIMethodInterface $method;

    public function __construct(APIMethodInterface $method)
    {
        $this->method = $method;
    }

    public function getID(): string
    {
        return $this->getVersion();
    }

    public function getMethod() : APIMethodInterface
    {
        return $this->method;
    }

    public function getChangelog(): string
    {
        $text = trim($this->_getChangelog());

        $deprecated = $this->getDeprecatedKeys();
        if(!empty($deprecated)) {
            if(!empty($text)) {
                $text .= PHP_EOL.PHP_EOL;
            }

            $text .= '### Deprecations'.PHP_EOL.
                PHP_EOL.
                'The following fields are deprecated and will be removed in future versions:'.PHP_EOL.
                PHP_EOL.
                $this->renderKeysList($deprecated);
        }

        $text = trim($text);

        $removed = $this->getRemovedKeys();
        if(!empty($removed)) {
            if(!empty($text)) {
                $text .= PHP_EOL.PHP_EOL;
            }

            $text .= "### Removals".PHP_EOL.
                PHP_EOL.
                "The following fields have been removed:".PHP_EOL.
                PHP_EOL.
                $this->renderKeysList($removed);
        }

        return trim($text);
    }

    /**
     * @param array<int,KeyPathInterface> $keys
     * @return string
     */
    private function renderKeysList(array $keys) : string
    {
        $text = "";

        if(empty($keys)) {
            return $text;
        }

        usort($keys, static function(KeyPathInterface $a, KeyPathInterface $b) : int {
            return strnatcasecmp($a->getPath(), $b->getPath());
        });

        foreach($keys as $key)
        {
            if($key instanceof KeyReplacement) {
                $path = $key->getOldKey();
                $replacement = $key->getNewKey();
            } else {
                $path = (string)$key;
                $replacement = null;
            }

            $text .= sprintf("- `%s`", $path);
            if($replacement !== null) {
                $text .= sprintf(" (use `%s` instead)", $replacement);
            }

            $text .= PHP_EOL;
        }

        return $text;
    }

    /**
     * Markdown-formatted changelog of this version.
     *
     * Use {@see self::getRemovedKeys()} and {@see self::getDeprecatedKeys()} for changes
     * that are automatically appended to the changelog.
     *
     * @return string
     */
    abstract protected function _getChangelog() : string;
}
