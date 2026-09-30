<?php

namespace Tests\Unit\Datagrid;

use RPC\Datagrid\Pager;
use Tests\Unit\UnitTestCase;

class PagerTest extends UnitTestCase
{
    private Pager $pager;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear GET parameters
        $_GET = [];

        $this->pager = new Pager();
    }

    public function testPagerConstruction()
    {
        $pager = new Pager();

        $this->assertInstanceOf(Pager::class, $pager);
    }

    public function testSetTotal()
    {
        $result = $this->pager->setTotal(100);

        $this->assertInstanceOf(Pager::class, $result); // Fluent interface
        $this->assertEquals(100, $this->pager->getTotalRows());
    }

    public function testSetCurrent()
    {
        $result = $this->pager->setCurrent(5);

        $this->assertInstanceOf(Pager::class, $result);
        $this->assertEquals(5, $this->pager->getCurrentPage());
    }

    public function testSetPerPage()
    {
        $result = $this->pager->setPerPage(25);

        $this->assertInstanceOf(Pager::class, $result);
        $this->assertEquals(25, $this->pager->getPerPage());
    }

    public function testGetPerPageDefault()
    {
        $this->assertEquals(50, $this->pager->getPerPage());
    }

    public function testSetPerPageIgnoresNegativeValues()
    {
        $this->pager->setPerPage(-10);

        // Should keep default value
        $this->assertEquals(50, $this->pager->getPerPage());
    }

    public function testSetPerPageIgnoresZero()
    {
        $this->pager->setPerPage(0);

        // Should keep default value
        $this->assertEquals(50, $this->pager->getPerPage());
    }

    public function testGetTotalPages()
    {
        $this->pager->setTotal(125);
        $this->pager->setPerPage(25);

        $this->assertEquals(5, $this->pager->getTotalPages());
    }

    public function testGetTotalPagesWithRemainder()
    {
        $this->pager->setTotal(126);
        $this->pager->setPerPage(25);

        // Should ceil: 126/25 = 5.04 → 6
        $this->assertEquals(6, $this->pager->getTotalPages());
    }

    public function testGetTotalPagesWithZeroTotal()
    {
        $this->pager->setTotal(0);

        $this->assertEquals(0, $this->pager->getTotalPages());
    }

    public function testGetLimits()
    {
        $this->pager->setPerPage(10);
        $this->pager->setCurrent(0);

        $limits = $this->pager->getLimits();

        $this->assertEquals([0, 10], $limits);
    }

    public function testGetLimitsForSecondPage()
    {
        $this->pager->setPerPage(10);
        $this->pager->setCurrent(1);

        $limits = $this->pager->getLimits();

        $this->assertEquals([10, 20], $limits);
    }

    public function testGetLimitsForThirdPage()
    {
        $this->pager->setPerPage(25);
        $this->pager->setCurrent(2);

        $limits = $this->pager->getLimits();

        $this->assertEquals([50, 75], $limits);
    }

    public function testGetCurrentPage()
    {
        $this->pager->setCurrent(3);

        $this->assertEquals(3, $this->pager->getCurrentPage());
    }

    public function testGetTotalRows()
    {
        $this->pager->setTotal(250);

        $this->assertEquals(250, $this->pager->getTotalRows());
    }

    public function testSetDelta()
    {
        $result = $this->pager->setDelta(7);

        // Should always return fluent interface
        $this->assertInstanceOf(Pager::class, $result);
    }

    public function testRenderReturnsEmptyStringWhenNoPages()
    {
        $this->pager->setTotal(0);

        $html = $this->pager->render();

        $this->assertEquals('', $html);
    }

    public function testRenderReturnsPaginationHtml()
    {
        $this->pager->setTotal(100);
        $this->pager->setPerPage(10);
        $this->pager->setCurrent(0);

        $html = $this->pager->render();

        $this->assertStringContainsString('<ul class="pagination">', $html);
        $this->assertStringContainsString('</ul>', $html);
    }

    public function testFluentInterface()
    {
        $result = $this->pager
            ->setTotal(100)
            ->setPerPage(20)
            ->setCurrent(2)
            ->setDelta(3);

        $this->assertInstanceOf(Pager::class, $result);
        $this->assertEquals(100, $this->pager->getTotalRows());
        $this->assertEquals(20, $this->pager->getPerPage());
        $this->assertEquals(2, $this->pager->getCurrentPage());
    }
}
