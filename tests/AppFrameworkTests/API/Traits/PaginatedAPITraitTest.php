<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Traits;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Traits\PaginatedAPIInterface;
use Application\Media\Collection\MediaFilterCriteria;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use ReflectionMethod;
use TestDriver\API\TestPaginatedMethod;

/**
 * Covers the pagination piece ({@see PaginatedAPIInterface}/
 * {@see \Application\API\Traits\PaginatedAPITrait}), exercised through
 * {@see TestPaginatedMethod} in `processReturn()`.
 */
final class PaginatedAPITraitTest extends APITestCase
{
    private function createMethod() : TestPaginatedMethod
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestPaginatedMethod::METHOD_NAME;

        return new TestPaginatedMethod(APIManager::getInstance());
    }

    // region: Defaults and explicit values

    public function test_defaultsWhenNotSubmitted() : void
    {
        $method = $this->createMethod();
        $data = $method->processReturn()->getData();

        $this->assertSame(10, $data[PaginatedAPIInterface::KEY_LIMIT]);
        $this->assertSame(0, $data[PaginatedAPIInterface::KEY_OFFSET]);
    }

    public function test_explicitValuesAreResolved() : void
    {
        $_REQUEST[PaginatedAPIInterface::PARAM_LIMIT] = '5';
        $_REQUEST[PaginatedAPIInterface::PARAM_OFFSET] = '20';

        $method = $this->createMethod();
        $data = $method->processReturn()->getData();

        $this->assertSame(5, $data[PaginatedAPIInterface::KEY_LIMIT]);
        $this->assertSame(20, $data[PaginatedAPIInterface::KEY_OFFSET]);
    }

    // endregion

    // region: Rejection, never clamping

    public function test_limitZeroIsRejected() : void
    {
        $_REQUEST[PaginatedAPIInterface::PARAM_LIMIT] = '0';

        $method = $this->createMethod();
        $data = $method->processReturn();

        $this->assertSame(PaginatedAPIInterface::VALIDATION_INVALID_LIMIT, $this->getValidationErrorCode($method));
        $this->assertErrorResponseCode($data, APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS);
    }

    public function test_limitAboveMaxIsRejected() : void
    {
        $_REQUEST[PaginatedAPIInterface::PARAM_LIMIT] = '26';

        $method = $this->createMethod();
        $data = $method->processReturn();

        $this->assertSame(PaginatedAPIInterface::VALIDATION_INVALID_LIMIT, $this->getValidationErrorCode($method));
        $this->assertErrorResponseCode($data, APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS);
    }

    public function test_limitAtMaxIsAccepted() : void
    {
        $_REQUEST[PaginatedAPIInterface::PARAM_LIMIT] = '25';

        $method = $this->createMethod();
        $data = $method->processReturn()->getData();

        $this->assertSame(25, $data[PaginatedAPIInterface::KEY_LIMIT]);
    }

    public function test_negativeOffsetIsRejected() : void
    {
        $_REQUEST[PaginatedAPIInterface::PARAM_OFFSET] = '-1';

        $method = $this->createMethod();
        $data = $method->processReturn();

        $this->assertSame(PaginatedAPIInterface::VALIDATION_INVALID_OFFSET, $this->getValidationErrorCode($method));
        $this->assertErrorResponseCode($data, APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS);
    }

    private function getValidationErrorCode(TestPaginatedMethod $method) : ?int
    {
        foreach($method->getValidationResults()->getResults() as $result) {
            if($result->isError()) {
                return $result->getCode();
            }
        }

        return null;
    }

    // endregion

    // region: Response keys

    public function test_responseContainsTotalCount() : void
    {
        $method = $this->createMethod();
        $data = $method->processReturn()->getData();

        $this->assertSame(TestPaginatedMethod::SYNTHETIC_TOTAL_COUNT, $data[PaginatedAPIInterface::KEY_TOTAL_COUNT]);
    }

    /**
     * Asserts the pagination response keys are present with the correct
     * values. A literal, direct key-order comparison is used instead of
     * the plan's suggested `ResponseKeySorter::sortKeys()` pattern: that
     * class does not exist anywhere in this codebase (verified by a
     * full-repository grep) — see the project incident comment recorded
     * for this work package. `addPaginationResponseKeys()`'s own,
     * documented write order (limit, offset, totalCount) is therefore
     * asserted directly.
     */
    public function test_responseKeysArePresentInDocumentedOrder() : void
    {
        $method = $this->createMethod();
        $data = $method->processReturn()->getData();

        $this->assertSame(
            array(
                PaginatedAPIInterface::KEY_LIMIT,
                PaginatedAPIInterface::KEY_OFFSET,
                PaginatedAPIInterface::KEY_TOTAL_COUNT,
            ),
            array_keys($data)
        );
    }

    // endregion

    // region: applyPagination() against a real filter criteria instance

    /**
     * Proves `applyPagination()` sets the limit/offset on a real
     * {@see \Application_FilterCriteria_Database} subclass through its
     * public `setLimit()` signature only, by reading the resulting SQL
     * `LIMIT` clause `buildLimit()` assembles (MySQL's `LIMIT offset,count`
     * form) via reflection — no query is executed.
     */
    public function test_applyPaginationSetsTheSQLLimitClause() : void
    {
        $_REQUEST[PaginatedAPIInterface::PARAM_LIMIT] = '5';
        $_REQUEST[PaginatedAPIInterface::PARAM_OFFSET] = '20';

        $method = $this->createMethod();

        // Resolve the parameters without sending a response.
        $this->assertTrue($method->getValidationResults()->isValid());

        $criteria = \Application\AppFactory::createMediaCollection()->getFilterCriteria();
        $this->assertInstanceOf(MediaFilterCriteria::class, $criteria);

        $method->applyPagination($criteria);

        $reflection = new ReflectionMethod($criteria, 'buildLimit');
        $sql = $reflection->invoke($criteria);

        $this->assertSame(' LIMIT 20,5', $sql);
    }

    // endregion
}
