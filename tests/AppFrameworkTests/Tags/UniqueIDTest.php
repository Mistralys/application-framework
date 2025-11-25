<?php
/**
 * @package Tagging
 * @subpackage Tests
 */

declare(strict_types=1);

namespace AppFrameworkTests\Tags;

use Application\Tags\Taggables\TaggableUniqueID;
use Mistralys\AppFrameworkTests\TestClasses\TaggingTestCase;

/**
 * @package Tagging
 * @subpackage Tests
 */
final class UniqueIDTest extends TaggingTestCase
{
    public function test_parse() : void
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'value' => '',
                'errorCode' => TaggableUniqueID::VALIDATION_MISSING_SEPARATOR
            ),
            array(
                'label' => 'Multiple separator chars',
                'value' => 'media.148.foo.bar',
                'errorCode' => TaggableUniqueID::VALIDATION_INCORRECT_AMOUNT_OF_TOKENS
            ),
            array(
                'label' => 'Unknown collection name',
                'value' => 'unknown.148',
                'errorCode' => TaggableUniqueID::VALIDATION_UNKNOWN_COLLECTION
            ),
            array(
                'label' => 'Non numeric record ID',
                'value' => 'media.foobar',
                'errorCode' => TaggableUniqueID::VALIDATION_NON_NUMERIC_RECORD_ID
            ),
            array(
                'label' => 'Non numeric record ID',
                'value' => 'media.147,5',
                'errorCode' => TaggableUniqueID::VALIDATION_NON_NUMERIC_RECORD_ID
            ),
            array(
                'label' => 'Negative record ID',
                'value' => 'media.-458',
                'errorCode' => TaggableUniqueID::VALIDATION_ZERO_OR_NEGATIVE_RECORD_ID
            ),
            array(
                'label' => 'Zero record ID',
                'value' => 'media.0',
                'errorCode' => TaggableUniqueID::VALIDATION_ZERO_OR_NEGATIVE_RECORD_ID
            ),
            array(
                'label' => 'Valid ID',
                'value' => 'media.148',
                'errorCode' => null
            ),
            array(
                'label' => 'Ignore whitespace',
                'value' => "   \n media    .  \t 148 \n",
                'errorCode' => null
            )
        );

        foreach($tests as $test)
        {
            $uniqueID = TaggableUniqueID::parse($test['value']);
            $message = $test['label'];

            if($test['errorCode'] === null) {
                $this->assertTrue($uniqueID->isValid(), $message);
            } else {
                $this->assertFalse($uniqueID->isValid(), $message);
                $this->assertSame($test['errorCode'], $uniqueID->getCode(), $message);
            }
        }
    }
}
