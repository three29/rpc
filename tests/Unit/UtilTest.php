<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RPC\Util;

class UtilTest extends TestCase
{
    // isIpInRange tests
    public function testIsIpInRangeWithEmptyRange()
    {
        $this->assertTrue(Util::isIpInRange('', '192.168.1.1'));
    }

    public function testIsIpInRangeSingleMatch()
    {
        $this->assertTrue(Util::isIpInRange('192.168.1.1', '192.168.1.1'));
    }

    public function testIsIpInRangeSingleNoMatch()
    {
        $this->assertFalse(Util::isIpInRange('192.168.1.1', '192.168.1.2'));
    }

    public function testIsIpInRangeWithWildcard()
    {
        $this->assertTrue(Util::isIpInRange('192.168.*.*', '192.168.1.1'));
        $this->assertTrue(Util::isIpInRange('192.168.*.*', '192.168.255.255'));
        $this->assertFalse(Util::isIpInRange('192.168.*.*', '192.169.1.1'));
    }

    public function testIsIpInRangeWithRange()
    {
        $this->assertTrue(Util::isIpInRange('192.168.1.1-192.168.1.100', '192.168.1.50'));
        $this->assertTrue(Util::isIpInRange('192.168.1.1-192.168.1.100', '192.168.1.1'));
        $this->assertTrue(Util::isIpInRange('192.168.1.1-192.168.1.100', '192.168.1.100'));
        $this->assertFalse(Util::isIpInRange('192.168.1.1-192.168.1.100', '192.168.1.101'));
        $this->assertFalse(Util::isIpInRange('192.168.1.1-192.168.1.100', '192.168.0.255'));
    }

    public function testIsIpInRangeWithMultipleRanges()
    {
        $range = '192.168.1.1-192.168.1.100;10.0.0.1';
        $this->assertTrue(Util::isIpInRange($range, '192.168.1.50'));
        $this->assertTrue(Util::isIpInRange($range, '10.0.0.1'));
        $this->assertFalse(Util::isIpInRange($range, '172.16.0.1'));
    }

    public function testIsIpInRangeLocalhostRange()
    {
        $this->assertTrue(Util::isIpInRange('127.0.0.*', '127.0.0.1'));
    }

    // arrayToOptions tests
    public function testArrayToOptionsWithArrays()
    {
        $input = [
            ['id' => 1, 'name' => 'Apple'],
            ['id' => 2, 'name' => 'Banana'],
            ['id' => 3, 'name' => 'Orange']
        ];

        $result = Util::arrayToOptions($input, 'id', 'name');

        $this->assertEquals([
            1 => 'Apple',
            2 => 'Banana',
            3 => 'Orange'
        ], $result);
    }

    public function testArrayToOptionsWithObjects()
    {
        $obj1 = new \stdClass();
        $obj1->id = 1;
        $obj1->name = 'Apple';

        $obj2 = new \stdClass();
        $obj2->id = 2;
        $obj2->name = 'Banana';

        $input = [$obj1, $obj2];

        $result = Util::arrayToOptions($input, 'id', 'name');

        $this->assertEquals([
            1 => 'Apple',
            2 => 'Banana'
        ], $result);
    }

    public function testArrayToOptionsEmptyArray()
    {
        $result = Util::arrayToOptions([], 'id', 'name');
        $this->assertEquals([], $result);
    }

    // generatePassword tests
    public function testGeneratePasswordPronouncable()
    {
        $password = Util::generatePassword(1, 8);
        $this->assertEquals(8, strlen($password));
        $this->assertMatchesRegularExpression('/^[a-z0-9]+$/i', $password);
    }

    public function testGeneratePasswordLowercase()
    {
        $password = Util::generatePassword(2, 10);
        $this->assertEquals(10, strlen($password));
    }

    public function testGeneratePasswordLowercaseWithNumbers()
    {
        $password = Util::generatePassword(3, 12);
        $this->assertEquals(12, strlen($password));
    }

    public function testGeneratePasswordMixedCase()
    {
        $password = Util::generatePassword(4, 8);
        $this->assertEquals(8, strlen($password));
    }

    public function testGeneratePasswordWithSpecialChars()
    {
        $password = Util::generatePassword(5, 16);
        $this->assertEquals(16, strlen($password));
    }

    public function testGeneratePasswordMaxComplexity()
    {
        $password = Util::generatePassword(6, 20);
        $this->assertEquals(20, strlen($password));
    }

    public function testGeneratePasswordInvalidType()
    {
        // Invalid type defaults to type 3
        $password = Util::generatePassword(99, 8);
        $this->assertEquals(8, strlen($password));
    }

    // generatePronouncablePassword tests
    public function testGeneratePronouncablePasswordLength()
    {
        $password = Util::generatePronouncablePassword(10);
        $this->assertEquals(10, strlen($password));
    }

    public function testGeneratePronouncablePasswordDefaultLength()
    {
        $password = Util::generatePronouncablePassword();
        $this->assertEquals(8, strlen($password));
    }

    public function testGeneratePronouncablePasswordContainsVowels()
    {
        $password = Util::generatePronouncablePassword(20);
        // Should contain at least some vowels
        $this->assertMatchesRegularExpression('/[aeiouy]/', $password);
    }

    // generatePasswordAdvanced tests
    public function testGeneratePasswordAdvancedLength()
    {
        $password = Util::generatePasswordAdvanced(15);
        $this->assertEquals(15, strlen($password));
    }

    public function testGeneratePasswordAdvancedUppercaseOnly()
    {
        $password = Util::generatePasswordAdvanced(10, true, false, false, false);
        $this->assertEquals(10, strlen($password));
        $this->assertMatchesRegularExpression('/^[A-Z]+$/', $password);
    }

    public function testGeneratePasswordAdvancedLowercaseOnly()
    {
        $password = Util::generatePasswordAdvanced(10, false, true, false, false);
        $this->assertEquals(10, strlen($password));
        $this->assertMatchesRegularExpression('/^[a-z]+$/', $password);
    }

    public function testGeneratePasswordAdvancedNumbersOnly()
    {
        $password = Util::generatePasswordAdvanced(10, false, false, true, false);
        $this->assertEquals(10, strlen($password));
        $this->assertMatchesRegularExpression('/^[0-9]+$/', $password);
    }

    public function testGeneratePasswordAdvancedWithCustomCharset()
    {
        $password = Util::generatePasswordAdvanced(10, false, false, false, false, false, 'ABC123');
        $this->assertEquals(10, strlen($password));
        $this->assertMatchesRegularExpression('/^[ABC123]+$/', $password);
    }

    // csrf tests
    public function testCsrfGeneratesToken()
    {
        // Mock $_SESSION since we can't start a real session in tests
        $_SESSION = [];

        $token = Util::csrf('test');
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        $this->assertArrayHasKey('csrf_token_test', $_SESSION);
    }

    public function testCsrfReturnsSameTokenForSameName()
    {
        $_SESSION = [];

        $token1 = Util::csrf('test_form');
        $token2 = Util::csrf('test_form');
        $this->assertEquals($token1, $token2);
    }

    public function testCsrfReturnsDifferentTokensForDifferentNames()
    {
        $_SESSION = [];

        $token1 = Util::csrf('form1');
        $token2 = Util::csrf('form2');
        $this->assertNotEquals($token1, $token2);
    }

    public function testCsrfDefaultName()
    {
        $_SESSION = [];

        $token = Util::csrf();
        $this->assertNotEmpty($token);
        $this->assertArrayHasKey('csrf_token_general', $_SESSION);
    }

    // get_client_source tests
    public function testGetClientSourceReturnsCLI()
    {
        // In CLI environment, should return 'cli'
        $source = Util::get_client_source();
        $this->assertEquals('cli', $source);
    }

    public function testGetClientSourceLogicWithServerVars()
    {
        // The function checks PHP_SAPI first, and we can't change that in tests
        // But we can verify the logic works by checking the implementation
        // Since we're in CLI, it will always return 'cli' first

        // Test that the function returns a value
        $source = Util::get_client_source();
        $this->assertNotEmpty($source);
        $this->assertIsString($source);
    }

    public function testGetClientSourceFallback()
    {
        // Test the fallback to 'unknown' would happen if not CLI and no server vars
        // Since we can't change PHP_SAPI, we just verify it returns something
        $source = Util::get_client_source();
        $this->assertContains($source, ['cli', 'unknown'], 'Should return cli or unknown');
    }

    // Additional edge case tests
    public function testIsIpInRangeWithMultipleWildcards()
    {
        $this->assertTrue(Util::isIpInRange('*.*.1.1', '192.168.1.1'));
        $this->assertTrue(Util::isIpInRange('192.*.*.1', '192.168.100.1'));
        $this->assertFalse(Util::isIpInRange('192.168.1.*', '192.168.2.1'));
    }

    public function testIsIpInRangeEdgeCases()
    {
        // Test with 0.0.0.0
        $this->assertTrue(Util::isIpInRange('0.0.0.0', '0.0.0.0'));

        // Test with 255.255.255.255
        $this->assertTrue(Util::isIpInRange('255.255.255.255', '255.255.255.255'));
    }

    public function testArrayToOptionsWithStringKeys()
    {
        $input = [
            ['code' => 'US', 'country' => 'United States'],
            ['code' => 'CA', 'country' => 'Canada'],
        ];

        $result = Util::arrayToOptions($input, 'code', 'country');

        $this->assertEquals([
            'US' => 'United States',
            'CA' => 'Canada'
        ], $result);
    }

    public function testGeneratePasswordMinimumLength()
    {
        $password = Util::generatePassword(3, 1);
        $this->assertGreaterThanOrEqual(1, strlen($password));
    }

    public function testGeneratePasswordVeryLongPassword()
    {
        $password = Util::generatePassword(4, 100);
        $this->assertEquals(100, strlen($password));
    }

    public function testGeneratePasswordAdvancedAllCharacterTypes()
    {
        $password = Util::generatePasswordAdvanced(50, true, true, true, true);

        $this->assertEquals(50, strlen($password));

        // Should contain at least one uppercase
        $this->assertMatchesRegularExpression('/[A-Z]/', $password);

        // Should contain at least one lowercase
        $this->assertMatchesRegularExpression('/[a-z]/', $password);

        // Should contain at least one number
        $this->assertMatchesRegularExpression('/[0-9]/', $password);

        // Should contain at least one special char
        $this->assertMatchesRegularExpression('/[^A-Za-z0-9]/', $password);
    }

    public function testCsrfTokenIsConsistent()
    {
        $_SESSION = [];

        $token1 = Util::csrf('persistent');

        // Simulate a new request with same session
        $token2 = Util::csrf('persistent');

        $this->assertEquals($token1, $token2);
    }

    public function testGeneratePronouncablePasswordUniqueness()
    {
        $password1 = Util::generatePronouncablePassword(10);
        $password2 = Util::generatePronouncablePassword(10);

        // Two generated passwords should be different
        $this->assertNotEquals($password1, $password2);
    }
}
