<?php

namespace Tests\Unit;

use RPC\Signal;
use Psr\EventDispatcher\StoppableEventInterface;

class SignalTest extends UnitTestCase
{
    private $signal;

    protected function setUp(): void
    {
        parent::setUp();

        // Get fresh instance for each test
        $this->signal = Signal::getInstance();
        $this->signal->flush(); // Clear any previous listeners
    }

    protected function tearDown(): void
    {
        $this->signal->flush();
        parent::tearDown();
    }

    public function testGetInstance()
    {
        $instance1 = Signal::getInstance();
        $instance2 = Signal::getInstance();

        $this->assertInstanceOf(Signal::class, $instance1);
        $this->assertSame($instance1, $instance2, 'Should return same singleton instance');
    }

    public function testListenRegistersListener()
    {
        $called = false;
        $this->signal->listen(TestEvent::class, function() use (&$called) {
            $called = true;
        });

        $this->assertTrue($this->signal->hasListeners(TestEvent::class));
    }

    public function testDispatchInvokesListeners()
    {
        $called = false;
        $this->signal->listen(TestEvent::class, function($event) use (&$called) {
            $called = true;
        });

        $event = new TestEvent();
        $this->signal->dispatch($event);

        $this->assertTrue($called);
    }

    public function testDispatchPassesEventToListener()
    {
        $receivedEvent = null;
        $this->signal->listen(TestEvent::class, function($event) use (&$receivedEvent) {
            $receivedEvent = $event;
        });

        $event = new TestEvent();
        $event->data = 'test data';

        $this->signal->dispatch($event);

        $this->assertSame($event, $receivedEvent);
        $this->assertEquals('test data', $receivedEvent->data);
    }

    public function testDispatchReturnsEvent()
    {
        $this->signal->listen(TestEvent::class, function($event) {
            $event->modified = true;
        });

        $event = new TestEvent();
        $returnedEvent = $this->signal->dispatch($event);

        $this->assertSame($event, $returnedEvent);
        $this->assertTrue($returnedEvent->modified);
    }

    public function testMultipleListenersCalledInOrder()
    {
        $callOrder = [];

        $this->signal->listen(TestEvent::class, function() use (&$callOrder) {
            $callOrder[] = 'first';
        });

        $this->signal->listen(TestEvent::class, function() use (&$callOrder) {
            $callOrder[] = 'second';
        });

        $this->signal->dispatch(new TestEvent());

        $this->assertCount(2, $callOrder);
        $this->assertEquals(['first', 'second'], $callOrder);
    }

    public function testListenerPriority()
    {
        $callOrder = [];

        $this->signal->listen(TestEvent::class, function() use (&$callOrder) {
            $callOrder[] = 'low';
        }, 1);

        $this->signal->listen(TestEvent::class, function() use (&$callOrder) {
            $callOrder[] = 'high';
        }, 10);

        $this->signal->listen(TestEvent::class, function() use (&$callOrder) {
            $callOrder[] = 'medium';
        }, 5);

        $this->signal->dispatch(new TestEvent());

        // Should be called in priority order: high (10), medium (5), low (1)
        $this->assertEquals(['high', 'medium', 'low'], $callOrder);
    }

    public function testStoppableEvent()
    {
        $callOrder = [];

        $this->signal->listen(StoppableTestEvent::class, function($event) use (&$callOrder) {
            $callOrder[] = 'first';
            $event->stopPropagation();
        });

        $this->signal->listen(StoppableTestEvent::class, function() use (&$callOrder) {
            $callOrder[] = 'second';
        });

        $this->signal->dispatch(new StoppableTestEvent());

        // Second listener should not be called
        $this->assertEquals(['first'], $callOrder);
    }

    public function testForgetRemovesListeners()
    {
        $this->signal->listen(TestEvent::class, function() {});

        $this->assertTrue($this->signal->hasListeners(TestEvent::class));

        $this->signal->forget(TestEvent::class);

        $this->assertFalse($this->signal->hasListeners(TestEvent::class));
    }

    public function testFlushRemovesAllListeners()
    {
        $this->signal->listen(TestEvent::class, function() {});
        $this->signal->listen(AnotherTestEvent::class, function() {});

        $this->assertTrue($this->signal->hasListeners(TestEvent::class));
        $this->assertTrue($this->signal->hasListeners(AnotherTestEvent::class));

        $this->signal->flush();

        $this->assertFalse($this->signal->hasListeners(TestEvent::class));
        $this->assertFalse($this->signal->hasListeners(AnotherTestEvent::class));
    }

    public function testHasListenersReturnsFalseForUnregisteredEvent()
    {
        $this->assertFalse($this->signal->hasListeners('NonexistentEvent'));
    }

    public function testGetListeners()
    {
        $listener1 = function() {};
        $listener2 = function() {};

        $this->signal->listen(TestEvent::class, $listener1);
        $this->signal->listen(TestEvent::class, $listener2);

        $listeners = $this->signal->getListeners(TestEvent::class);

        $this->assertCount(2, $listeners);
        $this->assertContains($listener1, $listeners);
        $this->assertContains($listener2, $listeners);
    }

    public function testGetListenersReturnsEmptyArrayForUnregisteredEvent()
    {
        $listeners = $this->signal->getListeners('NonexistentEvent');

        $this->assertIsArray($listeners);
        $this->assertCount(0, $listeners);
    }

    public function testDispatchWithNoListeners()
    {
        $event = new TestEvent();
        $returnedEvent = $this->signal->dispatch($event);

        // Should return the event unchanged
        $this->assertSame($event, $returnedEvent);
    }

    public function testListenerCanModifyEvent()
    {
        $this->signal->listen(TestEvent::class, function($event) {
            $event->data = 'modified';
        });

        $event = new TestEvent();
        $event->data = 'original';

        $this->signal->dispatch($event);

        $this->assertEquals('modified', $event->data);
    }

    public function testMultipleListenersCanModifyEvent()
    {
        $this->signal->listen(TestEvent::class, function($event) {
            $event->counter = ($event->counter ?? 0) + 1;
        });

        $this->signal->listen(TestEvent::class, function($event) {
            $event->counter = ($event->counter ?? 0) + 10;
        });

        $event = new TestEvent();
        $this->signal->dispatch($event);

        $this->assertEquals(11, $event->counter);
    }

    public function testDifferentEventsHaveSeparateListeners()
    {
        $testEventCalled = false;
        $anotherEventCalled = false;

        $this->signal->listen(TestEvent::class, function() use (&$testEventCalled) {
            $testEventCalled = true;
        });

        $this->signal->listen(AnotherTestEvent::class, function() use (&$anotherEventCalled) {
            $anotherEventCalled = true;
        });

        $this->signal->dispatch(new TestEvent());

        $this->assertTrue($testEventCalled);
        $this->assertFalse($anotherEventCalled);
    }
}

// Test event classes
class TestEvent
{
    public $data;
    public $modified = false;
    public $counter = 0;
}

class AnotherTestEvent
{
}

class StoppableTestEvent implements StoppableEventInterface
{
    private $stopped = false;

    public function stopPropagation(): void
    {
        $this->stopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }
}
