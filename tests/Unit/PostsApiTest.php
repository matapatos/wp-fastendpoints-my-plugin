<?php

/**
 * Holds unit tests for testing the post router.
 *
 * @since 1.0.0
 *
 * @license MIT
 */

declare(strict_types=1);

namespace MyPlugin\Tests\Unit;

use Mockery;
use Wp\FastEndpoints\Endpoint;
use Wp\FastEndpoints\Router;

afterEach(function () {
    Mockery::close();
});

test('Check that we are returning a Router instance', function () {
    $router = require \ROUTERS_DIR.'/Posts.php';
    expect($router)->toBeInstanceOf(Router::class);
})->group('api', 'posts');

test('Creating post endpoint has correct permissions and schema', function () {
    // Create endpoint mock
    $endpoint = Mockery::mock(Endpoint::class);
    $endpoint
        ->shouldReceive('schema')
        ->once()
        ->with('Posts/CreateOrUpdate')
        ->andReturnSelf();
    $endpoint
        ->shouldReceive('hasCap')
        ->once()
        ->with('publish_posts');
    // Create router
    $router = Mockery::mock(Router::class)
        ->makePartial();
    $router
        ->shouldReceive('post')
        ->once()
        ->with('/', Mockery::type('callable'))
        ->andReturn($endpoint);
    require \ROUTERS_DIR.'/Posts.php';
})->group('api', 'posts');

test('Retrieving post endpoint has correct permissions and schema', function () {
    // Create endpoint mock
    $endpoint = Mockery::mock(Endpoint::class);
    $endpoint
        ->shouldReceive('returns')
        ->once()
        ->with('Posts/Get')
        ->andReturnSelf();
    $endpoint
        ->shouldReceive('hasCap')
        ->once()
        ->with('read');
    // Create router
    $router = Mockery::mock(Router::class)
        ->makePartial();
    $router
        ->shouldReceive('get')
        ->once()
        ->with('(?P<post_id>[\d]+)', Mockery::type('callable'))
        ->andReturn($endpoint);
    require \ROUTERS_DIR.'/Posts.php';
})->group('api', 'posts');

test('Updating post endpoint has correct permissions and schema', function () {
    // Create endpoint mock
    $endpoint = Mockery::mock(Endpoint::class);
    $endpoint
        ->shouldReceive('schema')
        ->once()
        ->with('Posts/CreateOrUpdate')
        ->andReturnSelf();
    $endpoint
        ->shouldReceive('hasCap')
        ->once()
        ->with('edit_post', '{post_id}');
    // Create router
    $router = Mockery::mock(Router::class)
        ->makePartial();
    $router
        ->shouldReceive('put')
        ->once()
        ->with('(?P<post_id>[\d]+)', Mockery::type('callable'))
        ->andReturn($endpoint);
    require \ROUTERS_DIR.'/Posts.php';
})->group('api', 'posts');

test('Deleting post endpoint has correct permissions and schema', function () {
    // Create endpoint mock
    $endpoint = Mockery::mock(Endpoint::class);
    $endpoint
        ->shouldReceive('returns')
        ->once()
        ->with('Posts/Get')
        ->andReturnSelf();
    $endpoint
        ->shouldReceive('hasCap')
        ->once()
        ->with('delete_post', '{post_id}');
    // Create router
    $router = Mockery::mock(Router::class)
        ->makePartial();
    $router
        ->shouldReceive('delete')
        ->once()
        ->with('(?P<post_id>[\d]+)', Mockery::type('callable'))
        ->andReturn($endpoint);
    require \ROUTERS_DIR.'/Posts.php';
})->group('api', 'posts');
