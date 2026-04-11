<?php

namespace Tests\Unit;

use App\DataTransferObjects\ValidationResult;
use PHPUnit\Framework\TestCase;

class ValidationResultTest extends TestCase
{
    public function test_empty_result_passes(): void
    {
        $result = new ValidationResult;

        $this->assertTrue($result->passes());
        $this->assertFalse($result->fails());
        $this->assertEmpty($result->errors());
        $this->assertEmpty($result->warnings());
    }

    public function test_result_with_errors_fails(): void
    {
        $result = new ValidationResult(
            errors: [
                ['field' => 'issue_date', 'message' => 'Issue date is required.', 'legal_ref' => null],
            ]
        );

        $this->assertTrue($result->fails());
        $this->assertFalse($result->passes());
        $this->assertCount(1, $result->errors());
    }

    public function test_result_with_only_warnings_passes(): void
    {
        $result = new ValidationResult(
            warnings: [
                ['field' => 'received_by_name', 'message' => 'Recommended for BG invoices.'],
            ]
        );

        $this->assertTrue($result->passes());
        $this->assertCount(1, $result->warnings());
    }

    public function test_merge_combines_two_results(): void
    {
        $a = new ValidationResult(
            errors: [['field' => 'a', 'message' => 'err a', 'legal_ref' => null]],
            warnings: [['field' => 'b', 'message' => 'warn b']],
        );
        $b = new ValidationResult(
            errors: [['field' => 'c', 'message' => 'err c', 'legal_ref' => null]],
        );

        $merged = $a->merge($b);

        $this->assertCount(2, $merged->errors());
        $this->assertCount(1, $merged->warnings());
    }

    public function test_errors_for_field_filters_correctly(): void
    {
        $result = new ValidationResult(
            errors: [
                ['field' => 'issue_date', 'message' => 'Required.', 'legal_ref' => null],
                ['field' => 'buyer_name', 'message' => 'Required.', 'legal_ref' => null],
                ['field' => 'issue_date', 'message' => 'Must be a date.', 'legal_ref' => null],
            ]
        );

        $this->assertCount(2, $result->errorsForField('issue_date'));
        $this->assertCount(1, $result->errorsForField('buyer_name'));
        $this->assertCount(0, $result->errorsForField('notes'));
    }
}
