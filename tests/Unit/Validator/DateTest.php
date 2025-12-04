<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\Date;

class DateTest extends TestCase
{
    public function testValidatesDefaultFormat()
    {
        $validator = new Date();
        $this->assertTrue($validator->validate('2023-12-25'));
    }

    public function testValidatesValidDate()
    {
        $validator = new Date('Y-m-d');
        $this->assertTrue($validator->validate('2023-01-15'));
    }

    public function testValidatesCustomFormat()
    {
        $validator = new Date('m/d/Y');
        $this->assertTrue($validator->validate('12/25/2023'));
    }

    public function testValidatesDifferentFormat()
    {
        $validator = new Date('d-m-Y');
        $this->assertTrue($validator->validate('25-12-2023'));
    }

    public function testRejectsInvalidDate()
    {
        $validator = new Date('Y-m-d');
        $this->assertFalse($validator->validate('2023-13-01')); // Invalid month
    }

    public function testRejectsInvalidDay()
    {
        $validator = new Date('Y-m-d');
        $this->assertFalse($validator->validate('2023-02-30')); // Feb 30 doesn't exist
    }

    public function testRejectsWrongFormat()
    {
        $validator = new Date('Y-m-d');
        $this->assertFalse($validator->validate('12/25/2023'));
    }

    public function testRejectsEmptyString()
    {
        $validator = new Date();
        $this->assertFalse($validator->validate(''));
    }

    public function testRejectsNonDateString()
    {
        $validator = new Date();
        $this->assertFalse($validator->validate('not a date'));
    }

    public function testErrorMessage()
    {
        $message = 'Invalid date format';
        $validator = new Date('Y-m-d', $message);
        $this->assertEquals($message, $validator->getError());
    }

    public function testValidatesLeapYearDate()
    {
        $validator = new Date('Y-m-d');
        $this->assertTrue($validator->validate('2024-02-29')); // 2024 is a leap year
    }

    public function testRejectsNonLeapYearDate()
    {
        $validator = new Date('Y-m-d');
        $this->assertFalse($validator->validate('2023-02-29')); // 2023 is not a leap year
    }
}
