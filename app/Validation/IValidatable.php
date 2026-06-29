<?php
namespace App\Validation;

/**
 * Interface implemented by objects which can be validated by {@link ValidationLog}.
 */
interface IValidatable
{
    /**
     * Should perform operations to validate the object that it's implemented on.
     *
     * @return string[] List of errors found while validating the object.
     */
    function validate(): array;
}
