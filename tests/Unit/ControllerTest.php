<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RPC\Controller;
use RPC\Registry;
use RPC\HTTP\Request;
use RPC\HTTP\Response;
use RPC\Router;

class ControllerTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock session for CSRF token generation
        $_SESSION = [];

        // Clear Registry before each test
        $this->clearRegistry();

        // Create a test controller instance
        $this->controller = new TestController();

        // Mock request and response
        $this->controller->request = $this->createMock(Request::class);
        $this->controller->response = $this->createMock(Response::class);
    }

    protected function tearDown(): void
    {
        $this->clearRegistry();
        parent::tearDown();
    }

    private function clearRegistry()
    {
        // Clear global registry
        $GLOBALS['_RPC_REGISTRY_'] = [];

        // Clear Application container if it exists
        if (\RPC\Application::$app !== null) {
            \RPC\Application::$app->flush();
        }
    }

    // __set and __get tests
    public function testSetAndGetVariable()
    {
        $this->controller->testVar = 'test value';
        $this->assertEquals('test value', $this->controller->testVar);
    }

    public function testGetNonExistentVariable()
    {
        $this->assertNull($this->controller->nonExistent);
    }

    public function testSetThrowsExceptionForTemplateVariable()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('reserved to a template name');
        $this->controller->template_var = 'value';
    }

    public function testSetThrowsExceptionForTemplatePrefix()
    {
        $this->expectException(\Exception::class);
        $this->controller->templateSomething = 'value';
    }

    public function testSetMultipleVariables()
    {
        $this->controller->var1 = 'value1';
        $this->controller->var2 = 'value2';
        $this->controller->var3 = 'value3';

        $this->assertEquals('value1', $this->controller->var1);
        $this->assertEquals('value2', $this->controller->var2);
        $this->assertEquals('value3', $this->controller->var3);
    }

    // param tests
    public function testParamReturnsValueFromRequest()
    {
        $this->controller->request->method('getParam')
            ->with('test_param', null)
            ->willReturn('param_value');

        $result = $this->controller->param('test_param');
        $this->assertEquals('param_value', $result);
    }

    public function testParamReturnsDefaultValue()
    {
        $this->controller->request->method('getParam')
            ->with('missing_param', 'default')
            ->willReturn('default');

        $result = $this->controller->param('missing_param', 'default');
        $this->assertEquals('default', $result);
    }

    public function testParamWithNullName()
    {
        $this->controller->request->method('getParam')
            ->with(null, null)
            ->willReturn(null);

        $result = $this->controller->param();
        $this->assertNull($result);
    }

    // redirect tests
    public function testRedirectCallsResponseRedirect()
    {
        $this->controller->response->expects($this->once())
            ->method('redirect')
            ->with('/test/url');

        $this->controller->redirect('/test/url');
    }

    // json tests
    public function testJsonCallsResponseJson()
    {
        $data = ['key' => 'value'];

        $this->controller->response->expects($this->once())
            ->method('json')
            ->with($data);

        $this->controller->json($data);
    }

    public function testJsonWithEmptyArray()
    {
        $this->controller->response->expects($this->once())
            ->method('json')
            ->with([]);

        $this->controller->json();
    }

    // jsonSuccess tests
    public function testJsonSuccessCallsResponseJsonSuccess()
    {
        $data = ['result' => 'success'];

        $this->controller->response->expects($this->once())
            ->method('jsonSuccess')
            ->with($data);

        $this->controller->jsonSuccess($data);
    }

    public function testJsonSuccessWithEmptyData()
    {
        $this->controller->response->expects($this->once())
            ->method('jsonSuccess')
            ->with([]);

        $this->controller->jsonSuccess();
    }

    // jsonError tests
    public function testJsonErrorCallsResponseJsonError()
    {
        $this->controller->response->expects($this->once())
            ->method('jsonError')
            ->with('Error message', ['error_data' => 'value']);

        $this->controller->jsonError('Error message', ['error_data' => 'value']);
    }

    public function testJsonErrorWithOnlyMessage()
    {
        $this->controller->response->expects($this->once())
            ->method('jsonError')
            ->with('Error message', []);

        $this->controller->jsonError('Error message');
    }

    public function testJsonErrorWithEmptyMessage()
    {
        $this->controller->response->expects($this->once())
            ->method('jsonError')
            ->with('', []);

        $this->controller->jsonError();
    }

    // flash tests
    public function testFlashStoresMessage()
    {
        $_SESSION = [];

        $this->controller->flash('Test message', 'success', false);

        $this->assertArrayHasKey('_FLASH_', $_SESSION);
        $this->assertCount(1, $_SESSION['_FLASH_']);
        $this->assertEquals('Test message', $_SESSION['_FLASH_'][0]['message']);
        $this->assertEquals('success', $_SESSION['_FLASH_'][0]['message_type']);
        $this->assertEquals(0, $_SESSION['_FLASH_'][0]['persistent']);
    }

    public function testFlashStoresPersistentMessage()
    {
        $_SESSION = [];

        $this->controller->flash('Persistent message', 'info', true);

        $this->assertEquals(1, $_SESSION['_FLASH_'][0]['persistent']);
    }

    public function testFlashStoresMultipleMessages()
    {
        $_SESSION = [];

        $this->controller->flash('Message 1', 'success');
        $this->controller->flash('Message 2', 'error');

        $this->assertCount(2, $_SESSION['_FLASH_']);
    }

    public function testFlashRetrievesMessages()
    {
        $_SESSION['_FLASH_'] = [
            ['message' => 'Test 1', 'message_type' => 'success', 'persistent' => 0],
            ['message' => 'Test 2', 'message_type' => 'error', 'persistent' => 0]
        ];

        $messages = $this->controller->flash();

        $this->assertCount(2, $messages);
        $this->assertEquals('Test 1', $messages[0]['message']);
        $this->assertEquals('Test 2', $messages[1]['message']);
    }

    public function testFlashRemovesNonPersistentMessages()
    {
        $_SESSION['_FLASH_'] = [
            ['message' => 'Non-persistent', 'message_type' => 'success', 'persistent' => 0],
            ['message' => 'Persistent', 'message_type' => 'info', 'persistent' => 1]
        ];

        $messages = $this->controller->flash();

        // Should return both messages
        $this->assertCount(2, $messages);

        // But only persistent one should remain in session
        $this->assertCount(1, $_SESSION['_FLASH_']);
        // After unset, the array may not be re-indexed, so check values instead
        $remainingMessage = reset($_SESSION['_FLASH_']);
        $this->assertEquals('Persistent', $remainingMessage['message']);
    }

    public function testFlashReturnsEmptyArrayWhenNoMessages()
    {
        $_SESSION = [];

        $messages = $this->controller->flash();

        $this->assertIsArray($messages);
        $this->assertEmpty($messages);
    }

    public function testFlashWithOnlyMessage()
    {
        $_SESSION = [];

        $this->controller->flash('Simple message');

        $this->assertArrayHasKey('_FLASH_', $_SESSION);
        $this->assertNull($_SESSION['_FLASH_'][0]['message_type']);
    }
}

// Test controller class for testing
class TestController extends Controller
{
    // Expose protected methods for testing if needed
}
