<?php
declare(strict_types=1);

namespace Konecnyjakub\EventDispatcher;

use Konecnyjakub\EventDispatcher\Events\Event;
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
        $closure = function (Event $event) {
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
        $closure = function (Event $event) {
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
        $this->assertNoException(function () use ($validator) {
            $closure = function (Event $event): void {
            };
            $validator->validate($closure);
        });
        $this->assertNoException(function () use ($validator) {
            $invokableListener = new InvokableListener();
            $validator->validate($invokableListener);
        });
        $this->assertNoException(function () use ($validator) {
            $object = new class
            {
                public function listener(Event $event): void
                {
                }
            };
            $arrayListener = [$object, "listener", ];
            $validator->validate($arrayListener);
        });
        $this->assertThrowsException(function () use ($validator) {
            $validator->validate(function (Event $event, int $number) {
            });
        }, InvalidListenerException::class, "The callback has to accept exactly 1 parameter");

        $this->assertThrowsException(function () use ($validator) {
            $validator->validate(function (int $number) {
            });
        }, InvalidListenerException::class, "The callback's first parameter has to be a class name");
        $this->assertThrowsException(function () use ($validator) {
            $validator->validate(function (Event $event) {
            });
        }, InvalidListenerException::class, "The callback's return type has to explicitly set to void");
        $this->assertThrowsException(function () use ($validator) {
            $validator->validate(function (Event $event): null {
                return null;
            });
        }, InvalidListenerException::class, "The callback's return type has to explicitly set to void");
    }
}
