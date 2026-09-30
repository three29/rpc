<?php

namespace Tests\Unit\View\Filter;

use RPC\View\Filter\Error;
use RPC\View\Filter;
use Tests\Unit\UnitTestCase;

class ErrorTest extends UnitTestCase
{
    private $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new Error();
    }

    public function testClassExists()
    {
        $this->assertTrue(class_exists(Error::class));
    }

    public function testExtendsBaseFilter()
    {
        $this->assertInstanceOf(Filter::class, $this->filter);
    }

    public function testTransformsErrorTag()
    {
        $source = '<error id="username"></error>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('<?php if( $view->getError(', $result);
        $this->assertStringContainsString('username', $result);
    }

    public function testSetSingleError()
    {
        $this->filter->set('username', 'Invalid username');
        $this->assertEquals('Invalid username', $this->filter->get('username'));
    }

    public function testSetMultipleErrorsWithArray()
    {
        $errors = [
            'username' => 'Invalid username',
            'email' => 'Invalid email'
        ];

        $this->filter->set($errors);

        $this->assertEquals('Invalid username', $this->filter->get('username'));
        $this->assertEquals('Invalid email', $this->filter->get('email'));
    }

    public function testMagicSetAndGet()
    {
        $this->filter->password = 'Password too weak';
        $this->assertEquals('Password too weak', $this->filter->password);
    }

    public function testMagicIsset()
    {
        $this->filter->set('field', 'Error message');
        $this->assertTrue(isset($this->filter->field));
        $this->assertFalse(isset($this->filter->nonexistent));
    }

    public function testExistReturnsTrueWhenErrorsSet()
    {
        $this->filter->set('field', 'Error');
        $this->assertTrue($this->filter->exist());
    }

    public function testExistReturnsFalseWhenNoErrors()
    {
        $this->assertFalse($this->filter->exist());
    }

    public function testErrorTagWithClass()
    {
        $source = '<error id="username" class="my-error"></error>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('my-error', $result);
    }

    public function testHandlesEmptySource()
    {
        $result = $this->filter->filter('');
        $this->assertEquals('', $result);
    }

    public function testHandlesSourceWithoutErrorTags()
    {
        $source = '<div>No errors here</div>';
        $result = $this->filter->filter($source);

        $this->assertEquals($source, $result);
    }
}
