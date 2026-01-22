<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function test_required_rule_fails_when_field_is_missing(): void
    {
        $validator = new Validator([]);

        $result = $validator->validate(['email' => ['required']]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('email', $validator->getErrors());
    }

    public function test_required_rule_fails_when_field_is_empty(): void
    {
        $validator = new Validator(['email' => '']);

        $result = $validator->validate(['email' => ['required']]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('email', $validator->getErrors());
    }

    public function test_required_rule_passes_when_field_has_value(): void
    {
        $validator = new Validator(['email' => 'test@example.com']);

        $result = $validator->validate(['email' => ['required']]);

        $this->assertTrue($result);
        $this->assertEmpty($validator->getErrors());
    }

    public function test_email_rule_fails_for_invalid_email(): void
    {
        $validator = new Validator(['email' => 'invalid-email']);

        $result = $validator->validate(['email' => ['email']]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('email', $validator->getErrors());
    }

    public function test_email_rule_passes_for_valid_email(): void
    {
        $validator = new Validator(['email' => 'test@example.com']);

        $result = $validator->validate(['email' => ['email']]);

        $this->assertTrue($result);
        $this->assertEmpty($validator->getErrors());
    }

    public function test_min_rule_fails_when_value_is_too_short(): void
    {
        $validator = new Validator(['password' => '1234567']);

        $result = $validator->validate(['password' => ['min:8']]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('password', $validator->getErrors());
    }

    public function test_min_rule_passes_when_value_meets_minimum(): void
    {
        $validator = new Validator(['password' => '12345678']);

        $result = $validator->validate(['password' => ['min:8']]);

        $this->assertTrue($result);
        $this->assertEmpty($validator->getErrors());
    }

    public function test_max_rule_fails_when_value_exceeds_maximum(): void
    {
        $validator = new Validator(['name' => str_repeat('a', 101)]);

        $result = $validator->validate(['name' => ['max:100']]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $validator->getErrors());
    }

    public function test_max_rule_passes_when_value_within_maximum(): void
    {
        $validator = new Validator(['name' => str_repeat('a', 100)]);

        $result = $validator->validate(['name' => ['max:100']]);

        $this->assertTrue($result);
        $this->assertEmpty($validator->getErrors());
    }

    public function test_multiple_rules_on_single_field(): void
    {
        $validator = new Validator(['email' => '']);

        $result = $validator->validate(['email' => ['required', 'email']]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('email', $validator->getErrors());
    }

    public function test_multiple_fields_validation(): void
    {
        $validator = new Validator([
            'email' => 'test@example.com',
            'password' => '12345678',
            'first_name' => 'John'
        ]);

        $result = $validator->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
            'first_name' => ['required', 'max:100']
        ]);

        $this->assertTrue($result);
        $this->assertEmpty($validator->getErrors());
    }

    public function test_collects_all_validation_errors(): void
    {
        $validator = new Validator([
            'email' => 'invalid',
            'password' => '123'
        ]);

        $result = $validator->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8']
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('email', $validator->getErrors());
        $this->assertArrayHasKey('password', $validator->getErrors());
    }
}
