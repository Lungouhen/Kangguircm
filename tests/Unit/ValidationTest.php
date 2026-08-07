<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Helpers\Validation;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Validation helper class.
 *
 * Verifies all validation rules and error reporting.
 */
class ValidationTest extends TestCase
{
    private Validation $validator;

    protected function setUp(): void
    {
        $this->validator = new Validation();
    }

    public function test_required_rule_passes_with_value(): void
    {
        $result = $this->validator->validate(['name' => 'John'], ['name' => 'required']);
        $this->assertTrue($result);
        $this->assertEmpty($this->validator->errors());
    }

    public function test_required_rule_fails_with_empty(): void
    {
        $result = $this->validator->validate(['name' => ''], ['name' => 'required']);
        $this->assertFalse($result);
        $this->assertNotEmpty($this->validator->errors()['name']);
    }

    public function test_email_rule_validates_format(): void
    {
        $this->assertTrue($this->validator->validate(['email' => 'test@example.com'], ['email' => 'email']));
        $this->assertFalse($this->validator->validate(['email' => 'not-an-email'], ['email' => 'email']));
    }

    public function test_min_rule_checks_string_length(): void
    {
        $this->assertTrue($this->validator->validate(['name' => 'John'], ['name' => 'min:3']));
        $this->assertFalse($this->validator->validate(['name' => 'Jo'], ['name' => 'min:3']));
    }

    public function test_max_rule_checks_string_length(): void
    {
        $this->assertTrue($this->validator->validate(['name' => 'Jo'], ['name' => 'max:5']));
        $this->assertFalse($this->validator->validate(['name' => 'Very Long Name Here'], ['name' => 'max:5']));
    }

    public function test_numeric_rule(): void
    {
        $this->assertTrue($this->validator->validate(['age' => '25'], ['age' => 'numeric']));
        $this->assertTrue($this->validator->validate(['price' => '9.99'], ['price' => 'numeric']));
        $this->assertFalse($this->validator->validate(['age' => 'abc'], ['age' => 'numeric']));
    }

    public function test_in_rule(): void
    {
        $this->assertTrue($this->validator->validate(['status' => 'draft'], ['status' => 'in:draft,published']));
        $this->assertFalse($this->validator->validate(['status' => 'invalid'], ['status' => 'in:draft,published']));
    }

    public function test_date_rule(): void
    {
        $this->assertTrue($this->validator->validate(['date' => '2024-01-15'], ['date' => 'date']));
        $this->assertFalse($this->validator->validate(['date' => 'not-a-date'], ['date' => 'date']));
    }

    public function test_multiple_rules_combined(): void
    {
        $rules = ['name' => 'required|min:2|max:50'];
        $this->assertTrue($this->validator->validate(['name' => 'John Doe'], $rules));
        $this->assertFalse($this->validator->validate(['name' => ''], $rules));
        $this->assertFalse($this->validator->validate(['name' => 'J'], $rules));
    }

    public function test_first_error_returns_single_message(): void
    {
        $this->validator->validate(['email' => 'bad'], ['email' => 'required|email']);
        $error = $this->validator->firstError('email');
        $this->assertNotNull($error);
        $this->assertIsString($error);
    }

    public function test_first_error_returns_null_for_valid_field(): void
    {
        $this->validator->validate(['name' => 'John'], ['name' => 'required']);
        $this->assertNull($this->validator->firstError('name'));
    }
}
