<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RPC\Regex;

class RegexTest extends TestCase
{
    public function testConstructorSetsRegex(): void
    {
        $pattern = '/test/';
        $regex = new Regex($pattern);

        $this->assertSame($pattern, $regex->getRegex());
    }

    public function testGetRegex(): void
    {
        $pattern = '/[a-z]+/i';
        $regex = new Regex($pattern);

        $this->assertSame($pattern, $regex->getRegex());
    }

    public function testMatchWithSimplePattern(): void
    {
        $regex = new Regex('/\d+/');
        $matches = [];

        $result = $regex->match('abc 123 def 456', $matches);

        $this->assertSame(2, $result);
        $this->assertCount(2, $matches);
        $this->assertSame('123', $matches[0][0][0]);
        $this->assertSame('456', $matches[1][0][0]);
    }

    public function testMatchWithGroups(): void
    {
        $regex = new Regex('/(\d{3})-(\d{4})/');
        $matches = [];

        $result = $regex->match('Phone: 555-1234', $matches);

        $this->assertSame(1, $result);
        $this->assertSame('555-1234', $matches[0][0][0]);
        $this->assertSame('555', $matches[0][1][0]);
        $this->assertSame('1234', $matches[0][2][0]);
    }

    public function testMatchWithOffset(): void
    {
        $regex = new Regex('/test/');
        $matches = [];

        $result = $regex->match('test test test', $matches, 5);

        $this->assertSame(2, $result);
    }

    public function testMatchReturnsZeroWhenNoMatches(): void
    {
        $regex = new Regex('/xyz/');
        $matches = [];

        $result = $regex->match('abc 123', $matches);

        $this->assertSame(0, $result);
        $this->assertEmpty($matches);
    }

    public function testReplaceWithStringSubject(): void
    {
        $regex = new Regex('/\d+/');

        $result = $regex->replace('Price: 100 dollars', 'XXX');

        $this->assertSame('Price: XXX dollars', $result);
    }

    public function testReplaceWithLimit(): void
    {
        $regex = new Regex('/\d+/');

        $result = $regex->replace('1 2 3 4 5', 'X', 2);

        $this->assertSame('X X 3 4 5', $result);
    }

    public function testReplaceWithCount(): void
    {
        $regex = new Regex('/\d+/');
        $count = 0;

        $result = $regex->replace('a1b2c3', 'X', -1, $count);

        $this->assertSame('aXbXcX', $result);
        $this->assertSame(3, $count);
    }

    public function testReplaceWithArraySubject(): void
    {
        $regex = new Regex('/\d+/');

        $result = $regex->replace(['abc123', 'def456'], 'XXX');

        $this->assertIsArray($result);
        $this->assertSame(['abcXXX', 'defXXX'], $result);
    }

    public function testReplaceInvalidArrayReplacementThrowsError(): void
    {
        $regex = new Regex('/\d+/');

        $this->expectException(\TypeError::class);
        $regex->replace('Price: 100', ['X', 'Y']);
    }

    public function testToString(): void
    {
        $pattern = '/test pattern/i';
        $regex = new Regex($pattern);

        $this->assertSame($pattern, (string)$regex);
    }

    public function testConstantsAreDefined(): void
    {
        $this->assertIsString(Regex::URI);
        $this->assertIsString(Regex::DOMAIN);
        $this->assertIsString(Regex::NAME);
        $this->assertIsString(Regex::USERNAME);
        $this->assertIsString(Regex::CURRENCY);
        $this->assertIsString(Regex::PASSWORD);
        $this->assertIsString(Regex::EMAIL);
        $this->assertIsString(Regex::CSV_LINE);
        $this->assertIsString(Regex::VISA_CC);
        $this->assertIsString(Regex::MASTER_CC);
        $this->assertIsString(Regex::DISCOVER_CC);
        $this->assertIsString(Regex::AMEX_CC);
        $this->assertIsString(Regex::US_PHONE);
        $this->assertIsString(Regex::US_ZIP);
    }

    public function testUriConstantMatches(): void
    {
        $regex = new Regex(Regex::URI);
        $matches = [];

        $this->assertGreaterThan(0, $regex->match('https://example.com/path', $matches));
        $this->assertSame(0, $regex->match('not a uri', $matches));
    }

    public function testEmailConstantMatches(): void
    {
        $regex = new Regex(Regex::EMAIL);
        $matches = [];

        $this->assertGreaterThan(0, $regex->match('test@example.com', $matches));
        $this->assertSame(0, $regex->match('invalid.email', $matches));
    }

    public function testUsPhoneConstantMatches(): void
    {
        $regex = new Regex(Regex::US_PHONE);
        $matches = [];

        $this->assertGreaterThan(0, $regex->match('(555) 123-4567', $matches));
        $this->assertGreaterThan(0, $regex->match('555-123-4567', $matches));
        $this->assertGreaterThan(0, $regex->match('5551234567', $matches));
    }

    public function testUsZipConstantMatches(): void
    {
        $regex = new Regex(Regex::US_ZIP);
        $matches = [];

        $this->assertGreaterThan(0, $regex->match('12345', $matches));
        $this->assertGreaterThan(0, $regex->match('12345-6789', $matches));
        $this->assertSame(0, $regex->match('1234', $matches));
    }
}
