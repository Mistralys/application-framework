<?php

declare(strict_types=1);

class UI_Themes_Exception extends Application_Exception
{
    public const ERROR_THEME_PATH_CONTAINS_NAVIGATION = 177001;
    public const ERROR_INVALID_THEME_PATH = 177002;
    public const ERROR_UNRECOGNIZED_THEME_PATH = 177003;
}
