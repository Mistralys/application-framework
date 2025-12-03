<?php

declare(strict_types=1);

namespace Application\MarkdownRenderer\CustomTags;

use Application\API\APIManager;
use Application\MarkdownRenderer\BaseCustomTag;
use AppUtils\AttributeCollection;

/**
 * Detects API Documentation tags:
 *
 * `{api: GetSomething}`
 */
class APIMethodDocTag extends BaseCustomTag
{
    private string $methodName;

    public function __construct(string $matchedText, string $methodName)
    {
        $this->methodName = $methodName;

        parent::__construct($matchedText, AttributeCollection::create());
    }

    /**
     * @param string $subject
     * @return APIMethodDocTag[]
     */
    public static function findTags(string $subject) : array
    {
        if(!str_contains($subject, '{api')) {
            return array();
        }

        preg_match_all('/{api:\s*([a-z0-9]+)}/iU', $subject, $matches);

        $result = array();

        foreach($matches[0] as $idx => $matchedText)
        {
            $methodName = $matches[1][$idx];

            $result[] = new APIMethodDocTag(
                $matchedText,
                $methodName
            );
        }

        return $result;
    }

    public function render(): string
    {
        return sprintf(
            '[%1$s](%2$s)',
            $this->methodName,
            APIManager::getInstance()->adminURL()->methodDocumentation($this->methodName)
        );
    }
}
