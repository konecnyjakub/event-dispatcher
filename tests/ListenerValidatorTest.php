<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\EventDispatcher\Events\Event;
use Konecnyjakub\EventDispatcher\Events\TestStoppableEvent;
use MyTester\Attributes\TestSuite;
use MyTester\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

#[TestSuite("ListenerValidator")]
final class ListenerValidatorTest extends TestCase
{
    public function testGetListenerReflection(): void
    {
        $validator = new ListenerValidator();
        $closure = static function (Event $event) {
        };
        $invokableListener = new InvokableListener();
        $object = new class
        {
            public function listener(Event $event): void
            {
            }
        };
        $arrayListener = [$object, "listener", ];
        $this->assertType(ReflectionMethod::class, $validator->getListenerReflection($invokableListener));
        $this->assertType(ReflectionMethod::class, $validator->getListenerReflection($arrayListener));
        $this->assertType(ReflectionFunction::class, $validator->getListenerReflection($closure));
        $this->assertType(ReflectionFunction::class, $validator->getListenerReflection("pi"));
    }

    public function testGetListenerMetadataReflection(): void
    {
        $validator = new ListenerValidator();
        $closure = static function (Event $event) {
        };
        $invokableListener = new InvokableListener();
        $object = new class
        {
            public function listener(Event $event): void
            {
            }
        };
        $arrayListener = [$object, "listener", ];
        $this->assertType(ReflectionClass::class, $validator->getListenerMetadataReflection($invokableListener));
        $this->assertType(ReflectionMethod::class, $validator->getListenerMetadataReflection($arrayListener));
        $this->assertType(ReflectionFunction::class, $validator->getListenerMetadataReflection($closure));
        $this->assertType(ReflectionFunction::class, $validator->getListenerMetadataReflection("pi"));
    }

    public function testValidate(): void
    {
        $validator = new ListenerValidator();
        $this->assertNoException(static function () use ($validator) {
            $closure = static function (Event $event): void {
            };
            $validator->validate($closure);
        });
        $this->assertNoException(static function () use ($validator) {
            $closure = static function (Event $event): void {
            };
            $validator->validate($closure, Event::class);
        });
        $this->assertNoException(static function () use ($validator) {
            $invokableListener = new InvokableListener();
            $validator->validate($invokableListener);
        });
        $this->assertNoException(static function () use ($validator) {
            $object = new class
            {
                public function listener(Event $event): void
                {
                }
            };
            $arrayListener = [$object, "listener", ];
            $validator->validate($arrayListener);
        });
        $this->assertThrowsException(static function () use ($validator) {
            $validator->validate(static function (Event $event, int $number) {
            });
        }, InvalidListenerException::class, "The callback has to accept exactly 1 parameter");
        $this->assertThrowsException(static function () use ($validator) {
            $validator->validate(static function (int $number) {
            });
        }, InvalidListenerException::class, "The callback's first parameter has to be a class name");
        $this->assertThrowsException(static function () use ($validator) {
            $validator->validate(static function (TestStoppableEvent $event) {
            }, Event::class);
        }, InvalidListenerException::class, "The callback's first parameter has to be " . Event::class);
        $this->assertThrowsException(static function () use ($validator) {
            $validator->validate(static function (Event $event) {
            });
        }, InvalidListenerException::class, "The callback's return type has to be explicitly set to void");
        $this->assertThrowsException(static function () use ($validator) {
            $validator->validate(static fn (Event $event): null => null);
        }, InvalidListenerException::class, "The callback's return type has to be explicitly set to void");
    }
}
