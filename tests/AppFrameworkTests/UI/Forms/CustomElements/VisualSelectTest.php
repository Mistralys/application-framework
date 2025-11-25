<?php

declare(strict_types=1);

namespace ApplicationTests\TestSuites\UI\Forms\CustomElements;

use HTML_QuickForm2_Element_VisualSelect;
use HTML_QuickForm2_Element_VisualSelect_Optgroup;
use AppFrameworkTestClasses\ApplicationTestCase;
use UI\Form\Element\VisualSelect\VisualSelectOption;

final class VisualSelectTest extends ApplicationTestCase
{
    public function test_customOptionClass() : void
    {
        $el = new HTML_QuickForm2_Element_VisualSelect();

        $this->assertInstanceOf(VisualSelectOption::class, $el->addOption('Label', 'value'));
    }

    public function test_customOptionGroupClass() : void
    {
        $el = new HTML_QuickForm2_Element_VisualSelect();

        $this->assertInstanceOf(HTML_QuickForm2_Element_VisualSelect_Optgroup::class, $el->addOptgroup('Label'));
    }

    public function test_autoGroupingMode() : void
    {
        $el = new HTML_QuickForm2_Element_VisualSelect();

        $this->assertFalse($el->isGroupingEnabled());

        $el->addOptgroup('Label');

        $this->assertTrue($el->isGroupingEnabled());
    }

    public function test_getSetImageURL() : void
    {
        $el = new HTML_QuickForm2_Element_VisualSelect();

        $option = $el->addOption('Label', 'text');

        $this->assertEmpty($option->getImageURL());

        $option->setImageURL('https://foo.bar');

        $this->assertSame('https://foo.bar', $option->getImageURL());
        $this->assertStringContainsString('https://foo.bar', (string)$el);
    }

    public function test_addImage() : void
    {
        $el = new HTML_QuickForm2_Element_VisualSelect();

        $option = $el->addImage('Label', 'text', 'https://foo.bar');

        $this->assertSame('https://foo.bar', $option->getImageURL());
    }

    /**
     * When enabling the please select entry, it must be prepended
     * at the top of any existing options.
     */
    public function test_pleaseSelectOnTop() : void
    {
        $el = new HTML_QuickForm2_Element_VisualSelect();
        $el->addImage('Label', 'text', 'https://foo.bar');

        $this->assertFalse($el->isPleaseSelectEnabled());

        $el->setPleaseSelectEnabled(true, 'Select one');
        $this->assertTrue($el->isPleaseSelectEnabled());

        $this->assertStringContainsString('Select one', (string)$el);

        $options = $el->getOptionsFlat();
        $this->assertNotEmpty($options);
        $this->assertTrue($options[0]->isPleaseSelect());
    }

    /**
     * When there are enough options to enable the filtering
     * controls, the thumbnail size must automatically switch
     * to the small size used for long lists.
     */
    public function test_filteringEnabled() : void
    {
        $el = new HTML_QuickForm2_Element_VisualSelect();
        $el->addImage('Label', 'text', 'https://foo.bar');

        $el->setSmallThumbnailSize(33);
        $el->setLargeThumbnailSize(44);

        $this->assertFalse($el->isFilteringEnabled());
        $this->assertSame(44, $el->getThumbnailSize());

        $el->setFilterThreshold(1);

        $this->assertSame(1, $el->getFilterThreshold());
        $this->assertTrue($el->isFilteringEnabled());
        $this->assertSame(33, $el->getThumbnailSize());
    }

    public function test_makeCheckered() : void
    {
        $el = new HTML_QuickForm2_Element_VisualSelect();
        $el->addImage('Label', 'text', 'https://foo.bar');

        $this->assertFalse($el->isCheckered());
        $this->assertStringNotContainsString('checkered', (string)$el);

        $el->makeCheckered();

        $this->assertTrue($el->isCheckered());
        $this->assertStringContainsString('class="visel-item checkered"', (string)$el);
    }

    public function test_containerClasses() : void
    {
        $el = new HTML_QuickForm2_Element_VisualSelect();
        $el->addImage('Label', 'text', 'https://foo.bar');

        $el->addContainerClass('container-class');

        $this->assertStringContainsString('class="container-class', (string)$el);
    }

    public function test_makeBlock() : void
    {
        $el = new HTML_QuickForm2_Element_VisualSelect();
        $el->addImage('Label', 'text', 'https://foo.bar');

        $el->makeBlock();

        $this->assertStringContainsString('class="btn-block', (string)$el);
    }

    /**
     * The method {@see HTML_QuickForm2_Element_VisualSelect_Optgroup::addImage()}
     * declares its return type to be a {@see VisualSelectOption}, but there is
     * type check in place. It relies on the {@see \HTML_QuickForm2_Element_Select_OptionContainer::setOptionClass()}
     * method, so we test one of the custom methods to ensure we have the right
     * class instance.
     *
     * NOTE: An assertInstanceOf() would work, but from PHPStan's point of view,
     * it is unnecessary.
     */
    public function test_groupAddOption() : void
    {
        $el = new HTML_QuickForm2_Element_VisualSelect();

        $group = $el->addOptgroup('Group label');

        $image = $group->addImage('Label', 'text', 'https://foo.bar');

        $this->assertNull($image->getImageSetID());
    }

    public function test_imageSet() : void
    {
        $el = new HTML_QuickForm2_Element_VisualSelect();

        $set1 = $el->addImageSet('first', 'First set');
        $set1->addImage('Label one', 'text-one', 'https://foo.bar/first');

        $set2 = $el->addImageSet('second', 'Second set');
        $set2->addImage('Label two', 'text-two', 'https://foo.bar/second');

        $options = $el->getOptionsFlat();

        $this->assertCount(2, $options);
        $this->assertCount(2, $el->getImageSets());

        $this->assertNotNull($options[0]->getImageSetID());
        $this->assertTrue($options[0]->hasImageSet());

        $html = (string)$el;

        $this->assertStringContainsString('First set', $html);
        $this->assertStringContainsString('Second set', $html);
        $this->assertStringContainsString('text-one', $html);
        $this->assertStringContainsString('text-two', $html);
    }
}
