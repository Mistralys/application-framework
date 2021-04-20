<?php

declare(strict_types=1);

class Application_AjaxMethods_GetGridFullViewHTML_Grid
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $html;

    public function __construct(string $title, string $html)
    {
        $this->title = $title;
        $this->html = $html;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getHTML(): string
    {
        return $this->filterHTML($this->html);
    }

    private function filterHTML(string $html) : string
    {
        preg_match_all('%<a\b[^>]*>(.*?)</a>%si', $html, $result, PREG_PATTERN_ORDER);

        foreach($result[0] as $idx => $matchedText)
        {
            $html = str_replace($matchedText, $result[1][$idx], $html);
        }

        return $html;
    }
}
