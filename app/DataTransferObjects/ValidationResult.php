<?php

namespace App\DataTransferObjects;

class ValidationResult
{
    /**
     * @param  array<int, array{field: string, message: string, legal_ref: ?string}>  $errors
     * @param  array<int, array{field: string, message: string}>  $warnings
     */
    public function __construct(
        private array $errors = [],
        private array $warnings = [],
    ) {}

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return ! $this->passes();
    }

    /** @return array<int, array{field: string, message: string, legal_ref: ?string}> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @return array<int, array{field: string, message: string}> */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /** @return array<int, array{field: string, message: string, legal_ref: ?string}> */
    public function errorsForField(string $field): array
    {
        return array_values(array_filter(
            $this->errors,
            fn (array $error) => $error['field'] === $field,
        ));
    }

    public function merge(self $other): self
    {
        return new self(
            errors: array_merge($this->errors, $other->errors),
            warnings: array_merge($this->warnings, $other->warnings),
        );
    }
}
