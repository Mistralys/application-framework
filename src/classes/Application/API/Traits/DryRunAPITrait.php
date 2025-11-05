<?php
/**
 * @package API
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\API\Traits;

use Application\API\Traits\DryRun\DryRunAPIParam;

/**
 * Trait used to implement the interface {@see DryRunAPIInterface}.
 *
 * @package API
 * @subpackage Traits
 * @see DryRunAPIInterface
 */
trait DryRunAPITrait
{
    protected bool $dryRunSelected = false;
    private ?DryRunAPIParam $dryRunParam = null;

    public function getDryRunParam() : ?DryRunAPIParam
    {
        return $this->dryRunParam;
    }

    public function registerDryRunParam() : DryRunAPIParam
    {
        $this->dryRunParam = new DryRunAPIParam();
        $this->manageParams()->registerParam($this->dryRunParam);
        return $this->dryRunParam;
    }

    public function selectDryRun(bool $dryRun) : self
    {
        $this->dryRunSelected = $dryRun;
        return $this;
    }

    public function isDryRun() : bool
    {
        return $this->dryRunSelected || $this->getDryRunParam()?->getValue() === true;
    }
}
