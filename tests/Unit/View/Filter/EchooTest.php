<?php

namespace Tests\Unit\View\Filter;

use RPC\View\Filter\Echoo;
use RPC\View\Filter;
use Tests\Unit\UnitTestCase;

class EchooTest extends UnitTestCase
{
    private $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new Echoo();
    }

    public function testClassExists()
    {
        $this->assertTrue(class_exists(Echoo::class));
    }

    public function testExtendsBaseFilter()
    {
        $this->assertInstanceOf(Filter::class, $this->filter);
    }

    public function testTransformsShortEchoTag()
    {
        $source = '<?= $var ?>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('<?php echo $view->escape( $var ); ?>', $result);
    }

    public function testEscapesVariable()
    {
        $source = '<?= $username ?>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('$view->escape( $username )', $result);
    }

    public function testHandlesEmptySource()
    {
        $result = $this->filter->filter('');
        $this->assertEquals('', $result);
    }

    public function testHandlesSourceWithoutShortEcho()
    {
        $source = '<div>No short echo here</div>';
        $result = $this->filter->filter($source);

        $this->assertEquals($source, $result);
    }

    public function testTrimsVariableName()
    {
        $source = '<?=  $var  ?>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('$view->escape( $var ); ?>', $result);
    }

    public function testHandlesMultipleShortEchos()
    {
        $source = '<div><?= $name ?> - <?= $email ?></div>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('$view->escape( $name )', $result);
        $this->assertStringContainsString('$view->escape( $email )', $result);
    }

    public function testRemovesTrailingSemicolon()
    {
        $source = '<?= $var; ?>';
        $result = $this->filter->filter($source);

        // Should not have double semicolon
        $this->assertStringNotContainsString('$var; );', $result);
        $this->assertStringContainsString('$view->escape( $var )', $result);
    }

    public function testHandlesObjectPropertyAccess()
    {
        $source = '<?= $user->name ?>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('$view->escape( $user->name )', $result);
    }

    public function testHandlesArrayAccess()
    {
        $source = '<?= $data[\'key\'] ?>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('$view->escape( $data[\'key\'] )', $result);
    }
}
