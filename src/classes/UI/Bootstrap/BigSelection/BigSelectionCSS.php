<?php

declare(strict_types=1);

namespace UI\Bootstrap\BigSelection;

/**
 * CSS class constants for the BigSelection widget and its items.
 *
 * This class provides a centralized location for all CSS class names
 * used by the BigSelection widget, making it easy to maintain and adjust
 * the styling across the entire component.
 */
class BigSelectionCSS
{
    // Main widget classes
    public const string WIDGET = 'bigselection';
    public const string WIDGET_WRAPPER = 'bigselection-wrapper';
    public const string WIDGET_HEIGHT_LIMITED = 'bigselection-height-limited';
    public const string WIDGET_SIZE_SMALL = 'size-small';

    // Item classes
    public const string ITEM_ENTRY = 'bigselection-entry';
    public const string ITEM_HEADER = 'bigselection-header';
    public const string ITEM_SEPARATOR = 'bigselection-separator';

    // Regular item specific classes
    public const string ANCHOR = 'bigselection-anchor';
    public const string LABEL = 'bigselection-label';
    public const string DESCRIPTION = 'bigselection-description';

    // Meta controls
    public const string META_CONTROLS_LIST = 'bigselection-meta-controls';
    public const string META_CONTROL_ITEM = 'bigselection-meta-control';

    // Filtering related classes
    public const string FILTERING_ENABLED = 'bigselection-filtering-enabled';
    public const string FILTERING_CONTAINER = 'bigselection-filtering';
    public const string SEARCH_INPUT = 'bigselection-search-terms';
    public const string CLEAR_BUTTON = 'bigselection-clear-btn';

    // State classes
    public const string STATE_ACTIVE = 'active';

    // Resource paths

    /**
     * @see src/themes/default/js/ui/bigselection/static.js
     */
    public const string RESOURCES_JS_HANDLER = 'ui/bigselection/static.js';

    /**
     * @see src/themes/default/css/ui-bigselection.css
     */
    public const string RESOURCES_STYLE_SHEET = 'ui-bigselection.css';
}
