<?php

declare(strict_types=1);

namespace testsuites\UI\BigSelection;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application_Exception;
use UI\Bootstrap\BigSelection\BigSelectionCSS;
use UI\Bootstrap\BigSelection\BigSelectionWidget;
use UI\Bootstrap\BigSelection\Item\CheckableItem;
use UI_ClientResource;

/**
 * Tests for the checkable-item feature of BigSelectionWidget.
 *
 * @package Application
 * @subpackage Tests
 *
 * @see BigSelectionWidget
 * @see CheckableItem
 */
final class BigSelectionCheckableTest extends ApplicationTestCase
{
    // region: Setup / teardown

    /**
     * Form name used across tests that need one.
     * @var string
     */
    private string $formName = 'test_checkable_field';

    // endregion

    // region: Factory helpers

    private function createWidget(string $formName = '') : BigSelectionWidget
    {
        $widget = $this->createUI()->createBigSelection();

        if (!empty($formName)) {
            $widget->setFormName($formName);
        }

        return $widget;
    }

    // endregion

    // region: addCheckable

    /**
     * @see BigSelectionWidget::addCheckable()
     */
    public function test_addCheckable_returnsCheckableItemAndIncreasesCount() : void
    {
        $widget = $this->createWidget($this->formName);
        $initial = $widget->countItems();

        $item = $widget->addCheckable('Option A', 'val_a');

        $this->assertInstanceOf(CheckableItem::class, $item);
        $this->assertSame($initial + 1, $widget->countItems());
    }

    // endregion

    // region: prependCheckable

    /**
     * @see BigSelectionWidget::prependCheckable()
     */
    public function test_prependCheckable_insertsAtBeginning() : void
    {
        $widget = $this->createWidget($this->formName);
        $widget->addCheckable('Second', 'second');
        $widget->prependCheckable('First', 'first');

        $items = $widget->getCheckableItems();

        $this->assertCount(2, $items);
        $this->assertSame('first', $items[0]->getValue());
        $this->assertSame('second', $items[1]->getValue());
    }

    // endregion

    // region: CheckableItem rendering

    /**
     * @see CheckableItem::_render()
     */
    public function test_checkableItem_renderOutput() : void
    {
        $widget = $this->createWidget($this->formName);
        $widget->addCheckable('My Label', 'my_value');

        $html = $widget->render();

        $this->assertStringContainsString('bigselection-checkable', $html);
        $this->assertStringContainsString(
            'name="' . $this->formName . '[]"',
            $html
        );
        $this->assertStringContainsString('value="my_value"', $html);
        $this->assertStringContainsString('bigselection-checkbox', $html);
    }

    /**
     * Unchecked item must render its hidden input with the disabled attribute
     * so the value is excluded from the form submission by default.
     *
     * @see CheckableItem::_render()
     */
    public function test_unchecked_rendersDisabledInput() : void
    {
        $widget = $this->createWidget($this->formName);
        $widget->addCheckable('Unchecked', 'unchecked_val');

        $html = $widget->render();

        $this->assertStringContainsString('disabled', $html);
    }

    /**
     * makeSelected() must add the active class to the <li> and render the
     * hidden input without the disabled attribute.
     *
     * @see CheckableItem::makeSelected()
     */
    public function test_makeSelected_addsActiveClassAndEnablesInput() : void
    {
        $widget = $this->createWidget($this->formName);
        $widget->addCheckable('Selected', 'selected_val')->makeSelected();

        $html = $widget->render();

        $this->assertStringContainsString('active', $html);
        // The hidden input for the selected item must NOT carry disabled
        $this->assertMatchesRegularExpression(
            '/name="' . preg_quote($this->formName, '/') . '\[\]"[^>]*value="selected_val"(?![^>]*disabled)/',
            $html
        );
    }

    /**
     * Checkable items must render FontAwesome itemActive/itemInactive icons
     * inside the checkbox span, not Unicode ballot box characters.
     *
     * @see CheckableItem::_render()
     */
    public function test_checkableItem_rendersFontAwesomeIcons() : void
    {
        $widget = $this->createWidget($this->formName);
        $widget->addCheckable('Icon Test', 'icon_val');

        $html = $widget->render();

        $this->assertStringContainsString(BigSelectionCSS::CHECKBOX_ICON_UNCHECKED, $html);
        $this->assertStringContainsString(BigSelectionCSS::CHECKBOX_ICON_CHECKED, $html);
        $this->assertStringContainsString('fa-circle', $html);
    }

    // endregion

    // region: Form name management

    /**
     * @see BigSelectionWidget::setFormName()
     * @see BigSelectionWidget::getFormName()
     * @see BigSelectionWidget::hasFormName()
     */
    public function test_setFormName_storesAndRetrieves() : void
    {
        $widget = $this->createWidget();
        $widget->setFormName('myfield');

        $this->assertSame('myfield', $widget->getFormName());
        $this->assertTrue($widget->hasFormName());
    }

    /**
     * Rendering a widget that has checkable items but no form name must throw.
     *
     * @see BigSelectionWidget::ERROR_FORM_NAME_REQUIRED
     */
    public function test_renderWithoutFormName_throwsException() : void
    {
        $widget = $this->createWidget(); // no form name
        $widget->addCheckable('Item', 'val');

        $this->expectException(Application_Exception::class);
        $this->expectExceptionCode(BigSelectionWidget::ERROR_FORM_NAME_REQUIRED);

        $widget->render();
    }

    /**
     * A widget with only regular items, headers, and separators must render
     * successfully without a form name.
     */
    public function test_renderWithoutCheckable_noException() : void
    {
        $widget = $this->createWidget(); // no form name, no checkable items
        $widget->addItem('Regular item')->makeLinked('#');
        $widget->addHeader('A header');
        $widget->addSeparator();

        // Must not throw
        $html = $widget->render();
        $this->assertNotEmpty($html);
    }

    // endregion

    // region: getSubmittedValues

    /**
     * Only registered values present in the request must be returned.
     *
     * @see BigSelectionWidget::getSubmittedValues()
     */
    public function test_getSubmittedValues_filtersInvalidValues() : void
    {
        $widget = $this->createWidget($this->formName);
        $widget->addCheckable('Valid 1', 'valid1');
        $widget->addCheckable('Valid 2', 'valid2');
        $widget->addCheckable('Other', 'other');

        $_REQUEST[$this->formName] = array('valid1', 'invalid', 'valid2');

        $result = $widget->getSubmittedValues();

        $this->assertSame(array('valid1', 'valid2'), array_values($result));
    }

    /**
     * When no request parameter is present, an empty array must be returned.
     *
     * @see BigSelectionWidget::getSubmittedValues()
     */
    public function test_getSubmittedValues_noSubmission() : void
    {
        $widget = $this->createWidget($this->formName);
        $widget->addCheckable('Item', 'val');

        unset($_REQUEST[$this->formName]);

        $result = $widget->getSubmittedValues();

        $this->assertSame(array(), $result);
    }

    /**
     * When no form name is set, getSubmittedValues() must return empty without error.
     *
     * @see BigSelectionWidget::getSubmittedValues()
     */
    public function test_getSubmittedValues_noFormName() : void
    {
        $widget = $this->createWidget(); // no form name

        $result = $widget->getSubmittedValues();

        $this->assertSame(array(), $result);
    }

    // endregion

    // region: getCheckableItems

    /**
     * getCheckableItems() must return only CheckableItem instances when the
     * widget contains a mix of different item types.
     *
     * @see BigSelectionWidget::getCheckableItems()
     */
    public function test_getCheckableItems_mixedItems() : void
    {
        $widget = $this->createWidget($this->formName);
        $widget->addItem('Regular')->makeLinked('#');
        $widget->addHeader('Header');
        $widget->addSeparator();
        $widget->addCheckable('Check A', 'a');
        $widget->addCheckable('Check B', 'b');

        $checkable = $widget->getCheckableItems();

        $this->assertCount(2, $checkable);

        foreach ($checkable as $item) {
            $this->assertInstanceOf(CheckableItem::class, $item);
        }
    }

    // endregion

    // region: UI factory

    /**
     * @see UI::createBigSelection()
     */
    public function test_createBigSelection_withFormName() : void
    {
        $widget = $this->createUI()->createBigSelection('myfield');

        $this->assertSame('myfield', $widget->getFormName());
    }

    // endregion

    // region: Filtering / search terms

    /**
     * A CheckableItem's data-terms attribute must include both label and description
     * when filtering is enabled.
     *
     * @see CheckableItem::resolveSearchWords()
     */
    public function test_dataTerms_includes_labelAndDescription() : void
    {
        $widget = $this->createWidget($this->formName);
        $widget->enableFiltering();
        $widget->setFilteringThreshold(1);
        $widget->addCheckable('Alpha Label', 'alpha')
            ->setDescription('Beta Description');

        $html = $widget->render();

        $this->assertStringContainsString('data-terms=', $html);
        $this->assertStringContainsString('Alpha Label', $html);
        $this->assertStringContainsString('Beta Description', $html);
        // Assert the data-terms attribute specifically contains both values encoded together,
        // distinct from plain-text occurrences elsewhere in the rendered output.
        $this->assertMatchesRegularExpression(
            '/data-terms="Alpha Label Beta Description"/',
            $html
        );
    }

    // endregion

    // region: JS resource loading

    /**
     * After rendering a widget with checkable items, the resource manager must
     * have queued BigSelectionCSS::RESOURCES_JS_CHECKABLE (checkable.js).
     *
     * @see BigSelectionCSS::RESOURCES_JS_CHECKABLE
     */
    public function test_renderWithCheckable_queuesCheckableScript() : void
    {
        $ui = $this->createUI();
        $widget = $ui->createBigSelection($this->formName);
        $widget->addCheckable('Item', 'val');

        $widget->render();

        $urls = array_map(
            static fn(UI_ClientResource $r) => $r->getFileOrURL(),
            $ui->getResourceManager()->getJavascripts()
        );

        $this->assertContains(BigSelectionCSS::RESOURCES_JS_CHECKABLE, $urls);
    }

    /**
     * A widget that contains no checkable items must report hasCheckableItems()
     * as false — which is the template gate that prevents checkable.js from
     * being queued.
     *
     * @see BigSelectionWidget::hasCheckableItems()
     */
    public function test_noCheckableItems_hasCheckableItemsReturnsFalse() : void
    {
        $widget = $this->createWidget();
        $widget->addItem('Regular item')->makeLinked('#');

        $this->assertFalse($widget->hasCheckableItems());
    }

    // endregion
}
