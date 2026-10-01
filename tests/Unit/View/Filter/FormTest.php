<?php

namespace Tests\Unit\View\Filter;

use RPC\View\Filter\Form;
use RPC\View\Filter;
use Tests\Unit\UnitTestCase;

class FormTest extends UnitTestCase
{
    private $form;

    protected function setUp(): void
    {
        parent::setUp();
        $this->form = new Form();
    }

    public function testClassExists()
    {
        $this->assertTrue(class_exists(Form::class));
    }

    public function testExtendsBaseFilter()
    {
        $this->assertInstanceOf(Filter::class, $this->form);
    }

    public function testSetMethodPost()
    {
        $this->form->setMethod('post');
        $this->assertTrue(true); // No exception thrown
    }

    public function testSetMethodGet()
    {
        $this->form->setMethod('GET'); // Should accept case-insensitive
        $this->assertTrue(true); // No exception thrown
    }

    public function testSetMethodThrowsExceptionForInvalidMethod()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Method can only be GET or POST');

        $this->form->setMethod('PUT');
    }

    public function testEscapeMethod()
    {
        $result = $this->form->escape('<script>alert("xss")</script>');

        $this->assertStringContainsString('&lt;', $result);
        $this->assertStringContainsString('&gt;', $result);
        $this->assertStringNotContainsString('<script>', $result);
    }

    public function testEscapeQuotes()
    {
        $result = $this->form->escape('Test "quotes" and \'apostrophes\'');

        $this->assertStringContainsString('&quot;', $result);
        $this->assertStringContainsString('&#039;', $result);
    }

    public function testHasTextMethod()
    {
        $this->assertTrue(method_exists($this->form, 'text'));
    }

    public function testHasHiddenMethod()
    {
        $this->assertTrue(method_exists($this->form, 'hidden'));
    }

    public function testHasCheckboxMethod()
    {
        $this->assertTrue(method_exists($this->form, 'checkbox'));
    }

    public function testHasRadioMethod()
    {
        $this->assertTrue(method_exists($this->form, 'radio'));
    }

    public function testHasTextareaMethod()
    {
        $this->assertTrue(method_exists($this->form, 'textarea'));
    }

    public function testHasSelectMethod()
    {
        $this->assertTrue(method_exists($this->form, 'select'));
    }

    public function testAddFilter()
    {
        $mockFilter = $this->createMock(Filter::class);
        $this->form->addFilter($mockFilter);

        $this->assertTrue(true); // No exception thrown
    }

    public function testFilterMethodExists()
    {
        $this->assertTrue(method_exists($this->form, 'filter'));
    }

    public function testIsSubmittedMethod()
    {
        $this->assertTrue(method_exists($this->form, 'isSubmitted'));
    }

    public function testGetValueMethod()
    {
        $this->assertTrue(method_exists($this->form, 'getValue'));
    }

    private function postForm(array $post): Form
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $post;
        unset($GLOBALS['_RPC_']);

        $form = new Form();
        $form->setMethod('post');

        return $form;
    }

    public function testGetValueReadsNestedFields()
    {
        $form = $this->postForm(['u' => ['name' => 'bob', 'tags' => ['a', 'b']], 'plain' => 'x']);

        $this->assertSame('x', $form->getValue('plain'));
        $this->assertSame('bob', $form->getValue('u[name]'));
        $this->assertSame(['a', 'b'], $form->getValue('u[tags][]'));
        $this->assertSame('', $form->getValue('u[missing]'));
        $this->assertSame([], $form->getValue('missing[]'));
        $this->assertNull($form->getValue('missing'));
    }

    public function testGetValueNeverEvaluatesFieldNamesAsCode()
    {
        $form = $this->postForm(['u' => ['name' => 'bob']]);
        $GLOBALS['rpc_form_injection'] = false;

        $result = $form->getValue("u[x'.(\$GLOBALS['rpc_form_injection'] = true).'");

        $this->assertSame('', $result);
        $this->assertFalse($GLOBALS['rpc_form_injection']);
        unset($GLOBALS['rpc_form_injection']);
    }

    public function testTextRendersSubmittedArrayAsEmpty()
    {
        $form = $this->postForm(['name' => ['<script>']]);

        $this->assertSame('', $form->text('name'));
    }

    public function testSelectEscapesOptgroupLabels()
    {
        $html = $this->form->select('s', ['<script>x</script>' => ['1' => 'One']]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('label="&lt;script&gt;x&lt;/script&gt;"', $html);
    }
}
