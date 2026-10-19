<?php
/**
 * @package API
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\API\Traits;

use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\Type\IntegerParameter;
use Application\API\Utilities\KeyDescription;
use Application_FilterCriteria;
use AppUtils\ArrayDataCollection;
use AppUtils\OperationResult;

/**
 * Trait used to implement the interface {@see PaginatedAPIInterface}.
 *
 * @package API
 * @subpackage Traits
 * @see PaginatedAPIInterface
 */
trait PaginatedAPITrait
{
    private ?IntegerParameter $paginationLimitParam = null;
    private ?IntegerParameter $paginationOffsetParam = null;
    private int $paginationDefaultLimit = 10;

    public function registerPaginationParams(int $defaultLimit, int $maxLimit) : self
    {
        $this->paginationDefaultLimit = $defaultLimit;

        $this->paginationLimitParam = new IntegerParameter(PaginatedAPIInterface::PARAM_LIMIT, t('Limit'));
        $this->paginationLimitParam
            ->setDescription(
                'Maximum number of results to return. Must be between 1 and %1$s. Defaults to %2$s if not specified.',
                $maxLimit,
                $defaultLimit
            )
            ->validateByCallback(static function (int|float|bool|string|array $value, OperationResult $result, APIParameterInterface $param) use ($maxLimit) : void {
                $limit = (int)$value;

                if ($limit < 1 || $limit > $maxLimit) {
                    $result->makeError(
                        sprintf(
                            'The [%s] parameter must be between 1 and %s.',
                            $param->getName(),
                            $maxLimit
                        ),
                        PaginatedAPIInterface::VALIDATION_INVALID_LIMIT
                    );
                }
            });

        $this->manageParams()->registerParam($this->paginationLimitParam);

        $this->paginationOffsetParam = new IntegerParameter(PaginatedAPIInterface::PARAM_OFFSET, t('Offset'));
        $this->paginationOffsetParam
            ->setDescription('Zero-based offset into the result set. Defaults to 0 if not specified.')
            ->validateByCallback(static function (int|float|bool|string|array $value, OperationResult $result, APIParameterInterface $param) : void {
                if ((int)$value < 0) {
                    $result->makeError(
                        sprintf(
                            'The [%s] parameter must not be negative.',
                            $param->getName()
                        ),
                        PaginatedAPIInterface::VALIDATION_INVALID_OFFSET
                    );
                }
            });

        $this->manageParams()->registerParam($this->paginationOffsetParam);

        return $this;
    }

    /**
     * NOTE: {@see IntegerParameter::resolveValue()} resolves an absent
     * parameter to `null` regardless of any configured default value (its
     * own `setDefaultValue()` only ever takes effect through
     * {@see \Application\API\Parameters\BaseAPIParameter::getDefaultValue()},
     * which is reached only when validation itself fails — never merely
     * because the value is absent). The configured default is therefore
     * applied here explicitly, rather than relying on the parameter's own
     * default-value plumbing.
     */
    public function getPaginationLimit() : int
    {
        return $this->paginationLimitParam?->getValue() ?? $this->paginationDefaultLimit;
    }

    public function getPaginationOffset() : int
    {
        return $this->paginationOffsetParam?->getValue() ?? 0;
    }

    public function applyPagination(Application_FilterCriteria $criteria) : self
    {
        $criteria->setLimit($this->getPaginationLimit(), $this->getPaginationOffset());

        return $this;
    }

    public function addPaginationResponseKeys(ArrayDataCollection $response, int $totalCount) : self
    {
        $response->setKey(PaginatedAPIInterface::KEY_LIMIT, $this->getPaginationLimit());
        $response->setKey(PaginatedAPIInterface::KEY_OFFSET, $this->getPaginationOffset());
        $response->setKey(PaginatedAPIInterface::KEY_TOTAL_COUNT, $totalCount);

        return $this;
    }

    public function getPaginationKeyDescriptions() : array
    {
        return array(
            KeyDescription::create(
                PaginatedAPIInterface::KEY_LIMIT,
                'The maximum number of results returned.'
            ),
            KeyDescription::create(
                PaginatedAPIInterface::KEY_OFFSET,
                'The zero-based offset applied to the result set.'
            ),
            KeyDescription::create(
                PaginatedAPIInterface::KEY_TOTAL_COUNT,
                'Total number of matching results, independent of paging.'
            ),
        );
    }
}
