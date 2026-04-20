<?php

declare(strict_types=1);

namespace AppFrameworkTests\UI;

use AppFrameworkTestClasses\ApplicationTestCase;
use UI\Icons\IconInfo;
use UI_Icon;

/**
 * Unit tests for {@see IconInfo}.
 *
 * Tests cover getters, getFullIconName(), createIcon() factory,
 * getMethodName() camelCase conversion, and the static normaliseID() method.
 */
final class IconInfoTest extends ApplicationTestCase
{
    // -------------------------------------------------------------------------
    // AC#2 — test_getID
    // -------------------------------------------------------------------------

    public function test_getID() : void
    {
        $info = new IconInfo('my_icon', 'sun', 'far', false);

        $this->assertSame(
            'my_icon',
            $info->getID(),
            'getID() must return the normalised icon ID passed to the constructor.'
        );
    }

    // -------------------------------------------------------------------------
    // AC#3 — test_getPrefix
    // -------------------------------------------------------------------------

    public function test_getPrefix() : void
    {
        $info = new IconInfo('activate', 'sun', 'far', false);

        $this->assertSame(
            'far',
            $info->getPrefix(),
            'getPrefix() must return the FA prefix string passed to the constructor.'
        );
    }

    // -------------------------------------------------------------------------
    // AC#4 — test_getIconName
    // -------------------------------------------------------------------------

    public function test_getIconName() : void
    {
        $info = new IconInfo('add', 'plus-circle', 'fa', false);

        $this->assertSame(
            'plus-circle',
            $info->getIconName(),
            'getIconName() must return the FA icon name passed to the constructor.'
        );
    }

    // -------------------------------------------------------------------------
    // AC#5 — test_getFullIconName
    // -------------------------------------------------------------------------

    public function test_getFullIconName() : void
    {
        // With prefix: should produce "prefix:iconName"
        $withPrefix = new IconInfo('activate', 'sun', 'far', false);
        $this->assertSame(
            'far:sun',
            $withPrefix->getFullIconName(),
            'getFullIconName() must return "prefix:iconName" when a prefix is present.'
        );

        // Without prefix: should return icon name only
        $noPrefix = new IconInfo('planet', 'planet', '', true);
        $this->assertSame(
            'planet',
            $noPrefix->getFullIconName(),
            'getFullIconName() must return the icon name only when prefix is empty.'
        );
    }

    // -------------------------------------------------------------------------
    // AC#6 — test_createIcon
    // -------------------------------------------------------------------------

    public function test_createIcon() : void
    {
        // With prefix — setType($iconName, $prefix) path
        $withPrefix = new IconInfo('activate', 'sun', 'far', false);
        $icon = $withPrefix->createIcon();

        $this->assertInstanceOf(
            UI_Icon::class,
            $icon,
            'createIcon() must return a UI_Icon instance.'
        );
        $this->assertSame(
            'sun',
            $icon->getType(),
            'createIcon() must set the icon name as the type on UI_Icon.'
        );
        $this->assertSame(
            'far',
            $icon->getPrefix(),
            'createIcon() must pass the prefix to UI_Icon when it is non-empty.'
        );

        // Without prefix — setType($iconName) path; no prefix argument is passed
        $noPrefix = new IconInfo('planet', 'planet', '', true);
        $iconNoPrefix = $noPrefix->createIcon();

        $this->assertInstanceOf(
            UI_Icon::class,
            $iconNoPrefix,
            'createIcon() must return a UI_Icon instance even when prefix is empty.'
        );
        $this->assertSame(
            'planet',
            $iconNoPrefix->getType(),
            'createIcon() with no prefix must still set the icon name correctly.'
        );
    }

    // -------------------------------------------------------------------------
    // AC#7 — test_camelCaseConversion
    // -------------------------------------------------------------------------

    public function test_camelCaseConversion() : void
    {
        // Multi-part underscore ID
        $multi = new IconInfo('attention_required', 'exclamation-triangle', 'fa', false);
        $this->assertSame(
            'attentionRequired',
            $multi->getMethodName(),
            'getMethodName() must convert underscore-separated IDs to camelCase.'
        );

        // Single-segment ID (no underscores) — must remain unchanged
        $single = new IconInfo('add', 'plus-circle', 'fa', false);
        $this->assertSame(
            'add',
            $single->getMethodName(),
            'getMethodName() must return the ID unchanged when there are no underscores.'
        );

        // Three-segment ID
        $triple = new IconInfo('my_icon_name', 'star', 'fa', false);
        $this->assertSame(
            'myIconName',
            $triple->getMethodName(),
            'getMethodName() must correctly camelCase a three-segment ID.'
        );
    }

    // -------------------------------------------------------------------------
    // AC#8 — test_normaliseID
    // -------------------------------------------------------------------------

    public function test_normaliseID() : void
    {
        // Hyphens → underscores
        $this->assertSame(
            'time_tracker',
            IconInfo::normaliseID('time-tracker'),
            'normaliseID() must replace hyphens with underscores.'
        );

        // Spaces → underscores
        $this->assertSame(
            'my_icon_name',
            IconInfo::normaliseID('my icon name'),
            'normaliseID() must replace spaces with underscores.'
        );

        // Already normalised — no change
        $this->assertSame(
            'already_ok',
            IconInfo::normaliseID('already_ok'),
            'normaliseID() must leave already-normalised IDs unchanged.'
        );

        // Mixed hyphens and spaces
        $this->assertSame(
            'mixed_hyphen_and_space',
            IconInfo::normaliseID('mixed-hyphen and space'),
            'normaliseID() must replace both hyphens and spaces in the same string.'
        );
    }
}
