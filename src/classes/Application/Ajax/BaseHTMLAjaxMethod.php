<?php
/**
 * @package Application
 * @subpackage AJAX
 */

declare(strict_types=1);

namespace Application\Ajax;

use Application_AjaxHandler;
use Application_AjaxMethod;
use AppUtils\Interfaces\StringableInterface;

/**
 * Base class for AJAX methods that return HTML responses.
 *
 * @package Application
 * @subpackage AJAX
 */
abstract class BaseHTMLAjaxMethod extends Application_AjaxMethod
{
    public function __construct(Application_AjaxHandler $handler)
    {
        parent::__construct($handler);

        $this->setReturnFormatHTML();
    }

    public function processHTML() : void
    {
        $this->sendHTMLResponse(toString($this->renderHTML()));
    }

    /**
     * @return string|StringableInterface
     */
    abstract protected function renderHTML();
}
