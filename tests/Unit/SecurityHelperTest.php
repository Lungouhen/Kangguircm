<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Helpers\Security;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Security helper class.
 *
 * Validates sanitization, password hashing, and financial validation.
 */
class SecurityHelperTest extends TestCase
{
    public function test_sanitize_escapes_html(): void
    {
        $this->assertSame('&lt;script&gt;', Security::sanitize('<script>'));
        $this->assertSame('&amp;amp;', Security::sanitize('&amp;'));
        $this->assertSame('&quot;quoted&quot;', Security::sanitize('"quoted"'));
    }

    public function test_sanitize_trims_whitespace(): void
    {
        $this->assertSame('hello', Security::sanitize('  hello  '));
    }

    public function test_sanitize_array_recursive(): void
    {
        $input = ['<b>bold</b>', ['nested' => '<i>italic</i>']];
        $result = Security::sanitizeArray($input);

        $this->assertSame('&lt;b&gt;bold&lt;/b&gt;', $result[0]);
        $this->assertSame('&lt;i&gt;italic&lt;/i&gt;', $result[1]['nested']);
    }

    public function test_generate_token_returns_hex_string(): void
    {
        $token = Security::generateToken(16);
        $this->assertSame(32, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $token);
    }

    public function test_generate_token_is_unique(): void
    {
        $tokens = [];
        for ($i = 0; $i < 100; $i++) {
            $tokens[] = Security::generateToken();
        }
        $this->assertCount(100, array_unique($tokens));
    }

    public function test_password_hash_and_verify(): void
    {
        $password = 'SecureP@ss123!';
        $hash = Security::hashPassword($password);

        $this->assertNotSame($password, $hash);
        $this->assertTrue(Security::verifyPassword($password, $hash));
        $this->assertFalse(Security::verifyPassword('wrong-password', $hash));
    }

    public function test_validate_financial_accepts_valid_amounts(): void
    {
        $this->assertTrue(Security::validateFinancial(0));
        $this->assertTrue(Security::validateFinancial(100.50));
        $this->assertTrue(Security::validateFinancial(999999999.99));
    }

    public function test_validate_financial_rejects_invalid_amounts(): void
    {
        $this->assertFalse(Security::validateFinancial(-1));
        $this->assertFalse(Security::validateFinancial(1000000000));
    }

    public function test_format_financial(): void
    {
        $this->assertSame('100.50', Security::formatFinancial(100.5));
        $this->assertSame('0.00', Security::formatFinancial(0));
        $this->assertSame('999.99', Security::formatFinancial(999.99));
    }
}
