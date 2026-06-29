<?php
namespace App\Validation;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use Override;

/**
 * Logs validation errors, which can be arranged by sections.
 * Each section should represent a different operation:
 * - Retrieve configuration
 *  - The config file doesn't exist.
 * - Argument parsing
 *  - `since` has an invalid format.
 *  - `until` has an invalid format.
 */
class ValidationLog implements IteratorAggregate
{
    public const UNCATEGORIZED_NAME = '<Uncategorized>';
    private array $errors;
    private ?string $currentSection;

    public function __construct()
    {
        $this->errors = [];
        $this->currentSection = null;
    }


    public function registerError(string $message, ?string $section = null): self
    {
        if ($section)
            $this->currentSection = $section;

        //echo "registerError(): $section : $message" . PHP_EOL;

        $this->errors[$this->currentSection ?? self::UNCATEGORIZED_NAME][] = $message;
        return $this;
    }

    public function startSection(string $section): void
    {
        $this->currentSection = $section;
    }

    public function closeSection(): void
    {
        $this->currentSection = self::UNCATEGORIZED_NAME;
    }

    /**
     * Executes the {@link IValidatable::validate()} method and
     * adds all validation errors to the log. (They are added to the
     * current section).
     *
     * @param IValidatable $validatable Object to be validated.
     * @return bool Returns false if the validation of the object has returned
     * at least on error.
     */
    public function validate(IValidatable $validatable): bool
    {
        $validationErrors = $validatable->validate();
        foreach ($validationErrors as $validationError)
            $this->registerError($validationError);

        return empty($validationErrors);
    }

    /**
     * @return int Number of sections logged in the validation log.
     */
    public function countSections(): int
    {
        return count($this->errors);
    }

    /**
     * Retrieves the errors from the requested section.
     *
     * @return array List of errors associated with the requested section,
     * if the specified section name doesn't exist, an empty array will be returned.
     */
    public function getSectionErrors(string $sectionName): array
    {
        return $this->errors[$sectionName] ?? [];
    }

    public function isEmpty(): bool
    {
        return empty($this->errors);
    }

    public function getSectionNames(): array
    {
        return array_keys($this->errors);
    }

    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->errors);
    }
}
