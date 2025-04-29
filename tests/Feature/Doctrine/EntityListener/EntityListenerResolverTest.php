<?php

use Sonata\Doctrine\EntityListenerResolver;

beforeEach(function () {
    doctrineTest();
});

it('should resolve entity listeners from the container', function () {
    // Create a simple test listener class
    $testListener = new class () {
        public function __construct()
        {
        }
    };

    // Get the classname of our anonymous class
    $testListenerClass = $testListener::class;

    // Bind the test listener to the container
    app()->bind($testListenerClass, fn () => $testListener);

    // Create the resolver
    $resolver = app()->get(EntityListenerResolver::class);

    // Test resolving the listener
    $resolvedListener = $resolver->resolve($testListenerClass);

    expect($resolvedListener)->toBe($testListener);
});

it('should register entity listeners directly', function () {
    // Create a test listener
    $testListener = new class () {
        public function __construct()
        {
        }
    };

    // Get the classname of our anonymous class
    $testListenerClass = $testListener::class;

    // Create the resolver
    $resolver = app()->get(EntityListenerResolver::class);

    // Register the listener directly
    $resolver->register($testListener);

    // Test resolving the registered listener
    $resolvedListener = $resolver->resolve($testListenerClass);

    expect($resolvedListener)->toBe($testListener);
});

it('should clear specific entity listeners', function () {
    // Create test listeners
    $testListener1 = new class () {
        public function __construct()
        {
        }
    };
    $testListener2 = new class () {
        public function __construct()
        {
        }
    };

    // Get the classnames
    $testListener1Class = $testListener1::class;
    $testListener2Class = $testListener2::class;

    // Create the resolver
    $resolver = app()->get(EntityListenerResolver::class);

    // Register the listeners
    $resolver->register($testListener1);
    $resolver->register($testListener2);

    // Clear the first listener
    $resolver->clear($testListener1Class);

    // First listener should now be resolved from container
    $mockListener1 = new $testListener1Class();
    app()->bind($testListener1Class, fn () => $mockListener1);

    // Test resolving the listeners after clearing
    $resolvedListener1 = $resolver->resolve($testListener1Class);
    $resolvedListener2 = $resolver->resolve($testListener2Class);

    expect($resolvedListener1)->toBe($mockListener1);
    expect($resolvedListener1)->not->toBe($testListener1);
    expect($resolvedListener2)->toBe($testListener2);
});

it('should clear all entity listeners when no class name is provided', function () {
    // Create test listeners
    $testListener1 = new class () {
        public function __construct()
        {
        }
    };
    $testListener2 = new class () {
        public function __construct()
        {
        }
    };

    // Get the classnames
    $testListener1Class = $testListener1::class;
    $testListener2Class = $testListener2::class;

    // Create the resolver
    $resolver = app()->get(EntityListenerResolver::class);

    // Register the listeners
    $resolver->register($testListener1);
    $resolver->register($testListener2);

    // Create mock listeners to be returned by the container
    $mockListener1 = new $testListener1Class();
    $mockListener2 = new $testListener2Class();

    // Bind the mock listeners to the container
    app()->bind($testListener1Class, fn () => $mockListener1);
    app()->bind($testListener2Class, fn () => $mockListener2);

    // Clear all listeners
    $resolver->clear();

    // Test resolving the listeners after clearing
    $resolvedListener1 = $resolver->resolve($testListener1Class);
    $resolvedListener2 = $resolver->resolve($testListener2Class);

    expect($resolvedListener1)->toBe($mockListener1);
    expect($resolvedListener1)->not->toBe($testListener1);
    expect($resolvedListener2)->toBe($mockListener2);
    expect($resolvedListener2)->not->toBe($testListener2);
});

it('should trim backslashes from class names when resolving', function () {
    // Create a test listener
    $testListener = new class () {
        public function __construct()
        {
        }
    };

    // Get the classname of our anonymous class
    $testListenerClass = $testListener::class;
    $testListenerClassWithSlashes = '\\' . $testListenerClass . '\\';

    // Create the resolver
    $resolver = app()->get(EntityListenerResolver::class);

    // Register the listener
    $resolver->register($testListener);

    // Test resolving with backslashes
    $resolvedListener = $resolver->resolve($testListenerClassWithSlashes);

    expect($resolvedListener)->toBe($testListener);
});

it('should cache resolved listeners', function () {
    // Create a simple test listener class
    $testListener = new class () {
        public function __construct()
        {
        }
    };

    // Get the classname of our anonymous class
    $testListenerClass = $testListener::class;

    // Set up a container spy to count calls
    $callCount = 0;
    app()->bind($testListenerClass, function () use ($testListener, &$callCount) {
        $callCount++;
        return $testListener;
    });

    // Create the resolver
    $resolver = app()->get(EntityListenerResolver::class);

    // Call resolve multiple times
    $resolver->resolve($testListenerClass);
    $resolver->resolve($testListenerClass);
    $resolver->resolve($testListenerClass);

    // Container should only be called once
    expect($callCount)->toBe(1);
});
