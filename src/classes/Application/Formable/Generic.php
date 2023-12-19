<?php
/**
 * @package Application
 * @subpackage Formable
 */

declare(strict_types=1);

/**
 * Generic formable class, for creating forms without having to
 * create a dedicated class for each form.
 *
 * @package Application
 * @subpackage Formable
 */
class Application_Formable_Generic extends Application_Formable
{
    /**
     * Creates an instance of the generic formable, and initializes
     * the form with the given name.
     *
     * @param string $name
     * @param array<string,mixed> $defaultValues Optional default values. Can be set later using {@see Application_Interfaces_Formable::setDefaultFormValues()}.
     * @return self
     */
    public static function create(string $name, array $defaultValues = array()): self
    {
        $formable = new self();
        $formable->createFormableForm($name, $defaultValues);

        return $formable;
    }

    public function render(): string
    {
        return $this->renderFormable();
    }
}
