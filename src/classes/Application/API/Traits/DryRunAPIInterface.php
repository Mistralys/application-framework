<?php
/**
 * @package API
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\API\Traits;

use Application\API\APIMethodInterface;
use Application\API\Traits\DryRun\DryRunAPIParam;

/**
 * Interface for APIs that support dry run functionality.
 * A dry run allows the API to simulate an operation without
 * making any actual changes.
 *
 * Use the trait {@see DryRunAPITrait} to implement this interface.
 *
 * @package API
 * @subpackage Traits
 * @see DryRunAPITrait
 */
interface DryRunAPIInterface extends APIMethodInterface
{
    public function selectDryRun(bool $dryRun) : self;
    public function getDryRunParam() : ?DryRunAPIParam;
    public function registerDryRunParam() : DryRunAPIParam;
    public function isDryRun() : bool;
}
