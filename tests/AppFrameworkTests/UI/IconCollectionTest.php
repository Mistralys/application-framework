<?php

declare(strict_types=1);

namespace AppFrameworkTests\UI;

use AppFrameworkTestClasses\ApplicationTestCase;
use UI\Icons\IconCollection;
use UI\Icons\IconInfo;
use UI_Icon;

final class IconCollectionTest extends ApplicationTestCase
{
    // The test application's custom-icons.json defines exactly these two icons.
    private const CUSTOM_ICON_IDS = array('planet', 'revisionable');

    // A framework standard icon used for positive lookups.
    private const STANDARD_ICON_ID = 'add';

    protected function tearDown() : void
    {
        IconCollection::resetInstance();
    }

    /**
     * @return IconCollection
     */
    private function collection() : IconCollection
    {
        return IconCollection::getInstance();
    }

    // -------------------------------------------------------------------------
    // getAll()
    // -------------------------------------------------------------------------

    public function test_getAll_includesFrameworkIcons() : void
    {
        $all = $this->collection()->getAll();

        $this->assertNotEmpty($all);

        $ids = array_map(
            static function(IconInfo $icon) : string { return $icon->getID(); },
            $all
        );

        $this->assertContains(self::STANDARD_ICON_ID, $ids);
    }

    public function test_getAll_includesCustomIcons() : void
    {
        $all = $this->collection()->getAll();

        $ids = array_map(
            static function(IconInfo $icon) : string { return $icon->getID(); },
            $all
        );

        foreach(self::CUSTOM_ICON_IDS as $customID)
        {
            $this->assertContains($customID, $ids,
                'Expected custom icon [' . $customID . '] to be present in getAll().'
            );
        }
    }

    // -------------------------------------------------------------------------
    // getCustomIcons()
    // -------------------------------------------------------------------------

    public function test_getCustomIcons_returnsExactlyCustomIcons() : void
    {
        $custom = $this->collection()->getCustomIcons();

        $this->assertCount(count(self::CUSTOM_ICON_IDS), $custom);

        $returnedIDs = array_map(
            static function(IconInfo $icon) : string { return $icon->getID(); },
            $custom
        );

        sort($returnedIDs);
        $expected = self::CUSTOM_ICON_IDS;
        sort($expected);

        $this->assertSame($expected, $returnedIDs);
    }

    public function test_getCustomIcons_allMarkedAsCustom() : void
    {
        foreach($this->collection()->getCustomIcons() as $icon)
        {
            $this->assertTrue(
                $icon->isCustom(),
                'Icon [' . $icon->getID() . '] returned by getCustomIcons() should have isCustom() === true.'
            );
        }
    }

    // -------------------------------------------------------------------------
    // getStandardIcons()
    // -------------------------------------------------------------------------

    public function test_getStandardIcons_containsNoCustomIcons() : void
    {
        $standard = $this->collection()->getStandardIcons();

        $this->assertNotEmpty($standard);

        $ids = array_map(
            static function(IconInfo $icon) : string { return $icon->getID(); },
            $standard
        );

        foreach(self::CUSTOM_ICON_IDS as $customID)
        {
            $this->assertNotContains(
                $customID,
                $ids,
                'Custom icon [' . $customID . '] must not appear in getStandardIcons().'
            );
        }
    }

    public function test_getStandardIcons_allMarkedAsStandard() : void
    {
        foreach($this->collection()->getStandardIcons() as $icon)
        {
            $this->assertTrue(
                $icon->isStandard(),
                'Icon [' . $icon->getID() . '] returned by getStandardIcons() should have isStandard() === true.'
            );
        }
    }

    // -------------------------------------------------------------------------
    // getByID()
    // -------------------------------------------------------------------------

    public function test_getByID_customIcon_isCustom() : void
    {
        $icon = $this->collection()->getByID('planet');

        $this->assertTrue($icon->isCustom());
    }

    public function test_getByID_standardIcon_isStandard() : void
    {
        $icon = $this->collection()->getByID(self::STANDARD_ICON_ID);

        $this->assertTrue($icon->isStandard());
    }

    public function test_getByID_createIcon_returnsUIIcon() : void
    {
        $icon = $this->collection()->getByID(self::STANDARD_ICON_ID)->createIcon();

        $this->assertInstanceOf(UI_Icon::class, $icon);
    }

    public function test_getByID_createIcon_setsCorrectType() : void
    {
        // 'add' maps to icon name 'plus-circle' with no prefix (defaults to 'fa').
        $icon = $this->collection()->getByID(self::STANDARD_ICON_ID)->createIcon();

        $this->assertSame('plus-circle', $icon->getType());
        $this->assertSame('fa', $icon->getPrefix());
    }

    public function test_getByID_throwsForUnknownID() : void
    {
        $this->expectException(\RuntimeException::class);

        $this->collection()->getByID('nonexistent_icon_that_does_not_exist');
    }

    // -------------------------------------------------------------------------
    // idExists()
    // -------------------------------------------------------------------------

    public function test_idExists_returnsFalseForNonexistent() : void
    {
        $this->assertFalse($this->collection()->idExists('nonexistent'));
    }

    public function test_idExists_returnsTrueForStandardIcon() : void
    {
        $this->assertTrue($this->collection()->idExists(self::STANDARD_ICON_ID));
    }

    public function test_idExists_returnsTrueForCustomIcon() : void
    {
        $this->assertTrue($this->collection()->idExists('planet'));
    }
}
