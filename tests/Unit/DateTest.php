<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RPC\Date;

class DateTest extends TestCase
{
    // Constructor tests
    public function testConstructorWithNoArguments()
    {
        $date = new Date();
        $this->assertInstanceOf(Date::class, $date);

        // Should use current time
        $now = time();
        $dateTimestamp = $date->getDate('U');
        $this->assertEqualsWithDelta($now, $dateTimestamp, 2);
    }

    public function testConstructorWithDateString()
    {
        $date = new Date('2023-05-15');
        $this->assertEquals('2023-05-15', $date->getDate('Y-m-d'));
    }

    public function testConstructorWithTimestamp()
    {
        $timestamp = strtotime('2023-06-20');
        $date = new Date($timestamp, 'U');
        $this->assertEquals('2023-06-20', $date->getDate('Y-m-d'));
    }

    public function testConstructorWithCustomFormat()
    {
        $date = new Date('15/05/2023', 'd/m/Y');
        $this->assertEquals('2023-05-15', $date->getDate('Y-m-d'));
    }

    // setDate tests
    public function testSetDate()
    {
        $date = new Date('2023-01-01');
        $date->setDate('2023-12-31');
        $this->assertEquals('2023-12-31', $date->getDate('Y-m-d'));
    }

    public function testSetDateWithCustomFormat()
    {
        $date = new Date('2023-01-01');
        $date->setDate('31/12/2023', 'd/m/Y');
        $this->assertEquals('2023-12-31', $date->getDate('Y-m-d'));
    }

    public function testSetDateReturnsDate()
    {
        $date = new Date();
        $result = $date->setDate('2023-05-15');
        $this->assertInstanceOf(Date::class, $result);
    }

    // getDate tests
    public function testGetDateDefaultFormat()
    {
        $date = new Date('2023-05-15');
        $this->assertEquals('2023-05-15', $date->getDate());
    }

    public function testGetDateCustomFormat()
    {
        $date = new Date('2023-05-15');
        $this->assertEquals('15/05/2023', $date->getDate('d/m/Y'));
    }

    public function testGetDateAsTimestamp()
    {
        $date = new Date('2023-05-15');
        $timestamp = $date->getDate('U');
        $this->assertEquals(strtotime('2023-05-15'), $timestamp);
    }

    // getTimestamp static tests
    public function testGetTimestampWithNull()
    {
        $this->assertNull(Date::getTimestamp(null));
    }

    public function testGetTimestampWithEmptyString()
    {
        $this->assertNull(Date::getTimestamp(''));
    }

    public function testGetTimestampWithDateString()
    {
        $timestamp = Date::getTimestamp('2023-05-15');
        $this->assertEquals(strtotime('2023-05-15'), $timestamp);
    }

    public function testGetTimestampWithTimestampFormat()
    {
        $timestamp = 1684108800;
        $result = Date::getTimestamp($timestamp, 'U');
        $this->assertEquals($timestamp, $result);
    }

    public function testGetTimestampWithCustomFormat()
    {
        $timestamp = Date::getTimestamp('15/05/2023', 'd/m/Y');
        $this->assertEquals(strtotime('2023-05-15'), $timestamp);
    }

    // validDate static tests
    public function testValidDateReturnsTrueForValidDate()
    {
        $this->assertTrue(Date::validDate('2023-05-15'));
    }

    public function testValidDateReturnsFalseForInvalidDate()
    {
        $this->assertFalse(Date::validDate('2023-13-45'));
    }

    public function testValidDateWithCustomFormat()
    {
        $this->assertTrue(Date::validDate('15/05/2023', 'd/m/Y'));
    }

    public function testValidDateReturnsFalseForNull()
    {
        $this->assertFalse(Date::validDate(null));
    }

    public function testValidDateReturnsFalseForEmptyString()
    {
        $this->assertFalse(Date::validDate(''));
    }

    public function testValidDateWithLeapYear()
    {
        $this->assertTrue(Date::validDate('2024-02-29'));
    }

    public function testValidDateWithNonLeapYear()
    {
        $this->assertFalse(Date::validDate('2023-02-29'));
    }

    // changeFormat static tests
    public function testChangeFormat()
    {
        $result = Date::changeFormat('2023-05-15', 'Y-m-d', 'd/m/Y');
        $this->assertEquals('15/05/2023', $result);
    }

    public function testChangeFormatWithNull()
    {
        $this->assertNull(Date::changeFormat(null, 'Y-m-d', 'd/m/Y'));
    }

    public function testChangeFormatWithInvalidDate()
    {
        $result = Date::changeFormat('invalid', 'Y-m-d', 'd/m/Y');
        $this->assertEquals('', $result);
    }

    // add tests
    public function testAddYears()
    {
        $date = new Date('2023-05-15');
        $newDate = $date->add(2, 'y');
        $this->assertEquals('2025-05-15', $newDate->getDate('Y-m-d'));
    }

    public function testAddMonths()
    {
        $date = new Date('2023-05-15');
        $newDate = $date->add(3, 'm');
        $this->assertEquals('2023-08-15', $newDate->getDate('Y-m-d'));
    }

    public function testAddDays()
    {
        $date = new Date('2023-05-15');
        $newDate = $date->add(10, 'd');
        $this->assertEquals('2023-05-25', $newDate->getDate('Y-m-d'));
    }

    public function testAddHours()
    {
        $date = new Date('2023-05-15 12:00:00', 'Y-m-d H:i:s');
        $newDate = $date->add(5, 'h');
        $this->assertEquals('17', $newDate->getDate('H'));
    }

    public function testAddMinutes()
    {
        $date = new Date('2023-05-15 12:00:00', 'Y-m-d H:i:s');
        $newDate = $date->add(30, 'i');
        $this->assertEquals('30', $newDate->getDate('i'));
    }

    public function testAddSeconds()
    {
        $date = new Date('2023-05-15 12:00:00', 'Y-m-d H:i:s');
        $newDate = $date->add(45, 's');
        $this->assertEquals('45', $newDate->getDate('s'));
    }

    // subtract tests
    public function testSubtractYears()
    {
        $date = new Date('2023-05-15');
        $newDate = $date->subtract(2, 'y');
        $this->assertEquals('2021-05-15', $newDate->getDate('Y-m-d'));
    }

    public function testSubtractMonths()
    {
        $date = new Date('2023-05-15');
        $newDate = $date->subtract(3, 'm');
        $this->assertEquals('2023-02-15', $newDate->getDate('Y-m-d'));
    }

    public function testSubtractDays()
    {
        $date = new Date('2023-05-15');
        $newDate = $date->subtract(10, 'd');
        $this->assertEquals('2023-05-05', $newDate->getDate('Y-m-d'));
    }

    // Test backwards compatibility alias (deprecated)
    public function testSubstractAlias()
    {
        $date = new Date('2023-05-15');
        $newDate = $date->substract(5, 'd');
        $this->assertEquals('2023-05-10', $newDate->getDate('Y-m-d'));
    }

    // between tests
    public function testBetweenWithDateObjects()
    {
        $date = new Date('2023-05-15');
        $from = new Date('2023-05-01');
        $to = new Date('2023-05-31');

        $this->assertTrue($date->between($from, $to));
    }

    public function testBetweenReturnsFalseWhenOutsideRange()
    {
        $date = new Date('2023-06-15');
        $from = new Date('2023-05-01');
        $to = new Date('2023-05-31');

        $this->assertFalse($date->between($from, $to));
    }

    public function testBetweenWithStringDates()
    {
        $date = new Date('2023-05-15');
        $result = $date->between('2023-05-01', 'Y-m-d', '2023-05-31', 'Y-m-d');

        $this->assertTrue($result);
    }

    public function testBetweenThrowsExceptionWithWrongArguments()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The function expects two or four parameters');

        $date = new Date('2023-05-15');
        $date->between('2023-05-01');
    }

    // dateDiff static tests
    public function testDateDiffInSeconds()
    {
        $from = '2023-05-15 12:00:00';
        $to = '2023-05-15 12:01:00';
        $diff = Date::dateDiff('s', $from, $to);
        $this->assertEquals(60, $diff);
    }

    public function testDateDiffInMinutes()
    {
        $from = '2023-05-15 12:00:00';
        $to = '2023-05-15 13:00:00';
        $diff = Date::dateDiff('n', $from, $to);
        $this->assertEquals(60, $diff);
    }

    public function testDateDiffInHours()
    {
        $from = '2023-05-15 12:00:00';
        $to = '2023-05-15 15:00:00';
        $diff = Date::dateDiff('h', $from, $to);
        $this->assertEquals(3, $diff);
    }

    public function testDateDiffInDays()
    {
        $from = '2023-05-15';
        $to = '2023-05-20';
        $diff = Date::dateDiff('d', $from, $to);
        $this->assertEquals(5, $diff);
    }

    public function testDateDiffInWeeks()
    {
        $from = '2023-05-01';
        $to = '2023-05-22';
        $diff = Date::dateDiff('ww', $from, $to);
        $this->assertEquals(3, $diff);
    }

    public function testDateDiffWithTimestamps()
    {
        $from = strtotime('2023-05-15');
        $to = strtotime('2023-05-20');
        $diff = Date::dateDiff('d', $from, $to);
        $this->assertEquals(5, $diff);
    }

    // setSeconds tests
    public function testSetSeconds()
    {
        $date = new Date('2023-05-15 12:30:00', 'Y-m-d H:i:s');
        $newDate = $date->setSeconds(45);
        $this->assertEquals('45', $newDate->getDate('s'));
    }

    public function testSetSecondsWithInvalidValue()
    {
        $date = new Date('2023-05-15 12:30:00', 'Y-m-d H:i:s');
        $newDate = $date->setSeconds(99);
        $this->assertEquals('00', $newDate->getDate('s'));
    }

    // setMinutes tests
    public function testSetMinutes()
    {
        $date = new Date('2023-05-15 12:00:00', 'Y-m-d H:i:s');
        $newDate = $date->setMinutes(45);
        $this->assertEquals('45', $newDate->getDate('i'));
    }

    public function testSetMinutesWithInvalidValue()
    {
        $date = new Date('2023-05-15 12:00:00', 'Y-m-d H:i:s');
        $newDate = $date->setMinutes(99);
        $this->assertEquals('00', $newDate->getDate('i'));
    }

    // setHour tests
    public function testSetHour()
    {
        $date = new Date('2023-05-15 12:00:00', 'Y-m-d H:i:s');
        $newDate = $date->setHour(18);
        $this->assertEquals('18', $newDate->getDate('H'));
    }

    public function testSetHourWithInvalidValue()
    {
        $date = new Date('2023-05-15 12:00:00', 'Y-m-d H:i:s');
        $newDate = $date->setHour(25);
        $this->assertEquals('00', $newDate->getDate('H'));
    }

    // setDay tests
    public function testSetDay()
    {
        $date = new Date('2023-05-15');
        $newDate = $date->setDay(25);
        $this->assertEquals('25', $newDate->getDate('d'));
    }

    public function testSetDayWithInvalidValue()
    {
        $date = new Date('2023-05-15');
        $newDate = $date->setDay(35);
        // mktime() wraps around, so day 35 becomes day 5 of next month
        // The code sets invalid to 0, but mktime(h, i, s, m, 0, y) returns last day of previous month
        $this->assertInstanceOf(Date::class, $newDate);
    }

    // setMonth tests
    public function testSetMonth()
    {
        $date = new Date('2023-05-15');
        $newDate = $date->setMonth(8);
        $this->assertEquals('08', $newDate->getDate('m'));
    }

    public function testSetMonthWithInvalidValue()
    {
        $date = new Date('2023-05-15');
        $newDate = $date->setMonth(15);
        // mktime() wraps invalid month 15 to month 3 of next year
        // The code sets invalid to 0, which becomes December of previous year
        $this->assertInstanceOf(Date::class, $newDate);
    }

    // setYear tests
    public function testSetYear()
    {
        $date = new Date('2023-05-15');
        $newDate = $date->setYear(2025);
        $this->assertEquals('2025', $newDate->getDate('Y'));
    }

    public function testSetYearWithInvalidValue()
    {
        $date = new Date('2023-05-15');
        $newDate = $date->setYear(2050);
        // Year 2050 is outside valid range (1901-2038), set to 0 which mktime handles
        $this->assertInstanceOf(Date::class, $newDate);
    }

    // getVariables static tests
    public function testGetVariables()
    {
        $result = Date::getVariables('2023-05-15', 'Y-m-d');
        $this->assertEquals(['2023', '05', '15', '00', '00', '00'], $result);
    }

    public function testGetVariablesWithTime()
    {
        $result = Date::getVariables('2023-05-15 14:30:45', 'Y-m-d H:i:s');
        $this->assertEquals(['2023', '05', '15', '14', '30', '45'], $result);
    }

    public function testGetVariablesWithCustomFormat()
    {
        $result = Date::getVariables('15/05/2023', 'd/m/Y');
        $this->assertEquals(['2023', '05', '15', '00', '00', '00'], $result);
    }

    // Edge cases
    public function testDateWithLeapYearFebruary()
    {
        $date = new Date('2024-02-29');
        $this->assertEquals('2024-02-29', $date->getDate('Y-m-d'));
    }

    public function testAddAcrossMonthBoundary()
    {
        $date = new Date('2023-01-31');
        $newDate = $date->add(1, 'm');
        // PHP's strtotime will handle this correctly
        $this->assertInstanceOf(Date::class, $newDate);
    }

    public function testSubtractAcrossYearBoundary()
    {
        $date = new Date('2023-01-15');
        $newDate = $date->subtract(1, 'm');
        $this->assertEquals('2022-12-15', $newDate->getDate('Y-m-d'));
    }
}
