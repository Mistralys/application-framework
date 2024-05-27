<?php

declare(strict_types=1);

namespace AppFrameworkTests\Forms;

use Application_Exception;
use AppUtils\BaseException;
use AppFrameworkTestClasses\ApplicationTestCase;
use HTML_QuickForm2;
use HTML_QuickForm2_DataSource_Array;
use UI;
use UI_Form;
use function AppUtils\parseVariable;

final class ValidationTests extends ApplicationTestCase
{
    // region: _Tests

    public function test_validateEmail(): void
    {
        $this->assertTrue(UI_Form::validateEmail('foo@test.bar'));
        $this->assertFalse(UI_Form::validateEmail('foo'));
    }

    public function test_percent(): void
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'valid' => true,
                'filtered' => ''
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'valid' => true,
                'filtered' => ''
            ),
            array(
                'label' => 'Integer',
                'value' => 85,
                'valid' => true,
                'filtered' => '85'
            ),
            array(
                'label' => 'Above maximum',
                'value' => 850,
                'valid' => false,
                'filtered' => '850'
            ),
            array(
                'label' => 'Below minimum',
                'value' => -50,
                'valid' => false,
                'filtered' => '-50'
            ),
            array(
                'label' => 'Invalid value',
                'value' => 'Not a number',
                'valid' => false,
                'filtered' => 'Not a number'
            ),
            array(
                'label' => 'Float string (comma)',
                'value' => '45,89',
                'valid' => true,
                'filtered' => '45.89'
            ),
            array(
                'label' => 'Float string (dot)',
                'value' => '45.89',
                'valid' => true,
                'filtered' => '45.89'
            ),
            array(
                'label' => 'With whitespace',
                'value' => '   33    ',
                'valid' => true,
                'filtered' => '33'
            )
        );

        $this->runElementTests('Percent', $tests, function (UI_Form $form) {
            return $form->addPercent('element', 'Element');
        });
    }

    /**
     * When an element is valid, it must return the validated,
     * and filtered element value.
     */
    public function test_percent_submit_wrapper(): void
    {
        $formName = 'testform' . $this->getTestCounter();

        // The form tracking variable is only checked in the $_REQUEST array
        $_REQUEST = array(
            '_qf__form-' . $formName => ''
        );

        // Form data is accessed only in POST data
        $_POST = array(
            'element' => '   45   '
        );

        $form = UI::getInstance()->createForm($formName, array(
            'element' => '89'
        ));

        $this->assertEquals(UI_Form::FORM_PREFIX . $formName, $form->getForm()->getId());

        $el = $form->addPercent('element', 'Label');

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());
        $this->assertEquals('45', $el->getValue());
    }

    /**
     * When an element is not valid, it must leave the value
     * unchanged - except the conversion to string.
     */
    public function test_percent_submit_invalid(): void
    {
        $formName = 'testform' . $this->getTestCounter();

        // The form tracking variable is only checked in the $_REQUEST array
        $_REQUEST = array(
            '_qf__form-' . $formName => ''
        );

        // Form data is accessed only in POST data
        $_POST = array(
            'element' => 'Invalid'
        );

        $form = UI::getInstance()->createForm($formName, array(
            'element' => '89'
        ));

        $this->assertEquals(UI_Form::FORM_PREFIX . $formName, $form->getForm()->getId());

        $el = $form->addPercent('element', 'Label');

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());
        $this->assertEquals('Invalid', $el->getValue());
    }

    /**
     * Ensure that submitting an element via a raw QuickForm
     * instance works analogous to the UI_Form wrapper variant.
     */
    public function test_percent_submit(): void
    {
        $formName = 'testform' . $this->getTestCounter();

        $_REQUEST = array(
            '_qf__' . $formName => '',
        );

        $_POST = array(
            'element' => '45'
        );

        $form = new HTML_QuickForm2($formName, 'post', null, true);

        $defaultValues = new HTML_QuickForm2_DataSource_Array(array(
            'element' => '10'
        ));

        $form->addDataSource($defaultValues);

        $data = $form->getDataReason();
        $sources = $form->getDataSources();

        $this->assertTrue($data['trackVarFound']);
        $this->assertCount(2, $sources);

        $el = $form->addText('element');
        $el->setLabel('Label');

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->validate());
        $this->assertEquals('45', $el->getValue());
    }

    public function test_integer(): void
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'valid' => true,
                'filtered' => ''
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'valid' => true,
                'filtered' => ''
            ),
            array(
                'label' => 'Integer',
                'value' => 85,
                'valid' => true,
                'filtered' => '85'
            ),
            array(
                'label' => 'Above maximum',
                'value' => 1850,
                'valid' => false,
                'filtered' => '1850'
            ),
            array(
                'label' => 'Below minimum',
                'value' => -50,
                'valid' => false,
                'filtered' => '-50'
            ),
            array(
                'label' => 'Invalid value',
                'value' => 'Not a number',
                'valid' => false,
                'filtered' => 'Not a number'
            ),
            array(
                'label' => 'Float string (comma)',
                'value' => '45,89',
                'valid' => false,
                'filtered' => '45,89'
            ),
            array(
                'label' => 'Float string (dot)',
                'value' => '45.89',
                'valid' => false,
                'filtered' => '45.89'
            ),
            array(
                'label' => 'With whitespace',
                'value' => '   33    ',
                'valid' => true,
                'filtered' => '33'
            )
        );

        $this->runElementTests('Integer', $tests, function (UI_Form $form) {
            return $form->addInteger('element', 'Element', null, 0, 1000);
        });
    }

    public function test_isodate(): void
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'valid' => true,
                'filtered' => ''
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'valid' => true,
                'filtered' => ''
            ),
            array(
                'label' => 'Valid date',
                'value' => '2020-12-23',
                'valid' => true,
                'filtered' => '2020-12-23'
            ),
            array(
                'label' => 'With slashes',
                'value' => '2020/12/23',
                'valid' => true,
                'filtered' => '2020-12-23'
            ),
            array(
                'label' => 'Month does not exist',
                'value' => '2020/13/23',
                'valid' => false,
                'filtered' => '2020/13/23'
            ),
            array(
                'label' => 'Month does not exist',
                'value' => '2020/13/23',
                'valid' => false,
                'filtered' => '2020/13/23'
            ),
            array(
                'label' => 'Day does not exist in month',
                'value' => '2020/11/31',
                'valid' => false,
                'filtered' => '2020/11/31'
            )
        );

        $this->runElementTests('Date', $tests, function (UI_Form $form) {
            return $form->addISODate('element', 'Element');
        });
    }

    // endregion

    // region: Support methods

    private function runElementTests(string $title, array $tests, callable $elementCallback): void
    {
        foreach ($tests as $test) {
            $form = UI::getInstance()->createForm('test', array('element' => $test['value']));

            try {
                $el = $elementCallback($form);
            } catch (Application_Exception $e) {
                $this->failException($e);
            }

            $validator = $form->getElementValidator($el);

            $this->assertNotNull($validator);

            $result = $validator->validate($test['value']);
            $testLabel = sprintf(
                'Test: %s / %s' . PHP_EOL .
                'Validation message: %s' . PHP_EOL .
                'Value: [%s]',
                $title,
                $test['label'],
                $validator->getErrorMessage(),
                parseVariable($validator->getValue())->enableType()->toString()
            );

            $this->assertSame(
                $test['valid'],
                $result,
                $testLabel
            );

            $filtered = $validator->getFilteredValue($test['value']);
            $this->assertSame($test['filtered'], $filtered, $testLabel);

            $el->setValue($test['value']);
            $filtered = $el->getValue();

            $this->assertSame($test['filtered'], $filtered, $testLabel);
        }
    }

    /**
     * @param BaseException $e
     * @return never
     */
    private function failException(BaseException $e): void
    {
        $this->fail(sprintf(
            'Exception #%s: %s' . PHP_EOL .
            'Type: %s' . PHP_EOL .
            'Details: %s',
            $e->getCode(),
            $e->getMessage(),
            get_class($e),
            $e->getDetails()
        ));
    }

    // endregion
}
